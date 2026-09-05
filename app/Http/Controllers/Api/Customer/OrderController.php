<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\DeliveryMethodRate;
use App\Models\LawyerServicePricing;
use App\Models\LawyerServiceRegion;
use App\Models\Order;
use App\Models\Region;
use App\Services\ApiResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(
        protected ApiResponseService $response
    ) {
    }

    /**
     * Get customer's orders.
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $query = Order::query()
                ->where(
                    'customer_id',
                    $request->user()->id
                )
                ->with([
                    'lawyer.user:id,name,email',
                    'service:id,name,slug',
                    'country:id,name,code',
                    'region:id,name',
                    'lawyerServicePricing:id,lawyer_service_region_id,service_level,fee,currency,estimated_days',
                    'deliveryMethod:id,name,slug,type',
                ]);

            /*
             * Search order number.
             */
            if ($request->filled('search')) {

                $search = $request->input('search');

                $query->where(
                    'order_number',
                    'like',
                    "%{$search}%"
                );
            }

            /*
             * Filter status.
             */
            if ($request->filled('status')) {

                $query->where(
                    'status',
                    $request->input('status')
                );
            }

            /*
             * Filter payment status.
             */
            if ($request->filled('payment_status')) {

                $query->where(
                    'payment_status',
                    $request->input('payment_status')
                );
            }

            $perPage = min(
                (int) $request->input('per_page', 15),
                100
            );

            $orders = $query
                ->latest('id')
                ->paginate($perPage);

            return $this->response->success(
                $orders,
                'Orders retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve orders.',
                500
            );
        }
    }

    /**
     * Create a new order.
     *
     * At this stage the order is only created.
     * Payment and document upload happen later.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'lawyer_service_region_id' => [
                'required',
                'integer',
                'exists:lawyer_service_regions,id',
            ],

            'lawyer_service_pricing_id' => [
                'required',
                'integer',
                'exists:lawyer_service_pricings,id',
            ],

            'service_level' => [
                'required',
                Rule::in([
                    'standard',
                    'express',
                    'urgent',
                ]),
            ],

            'delivery_method_id' => [
                'required',
                'integer',
                'exists:delivery_methods,id',
            ],

            'customer_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        try {

            $customer = $request->user();

            $order = DB::transaction(
                function () use (
                    $validated,
                    $customer
                ) {

                    /*
                     * ------------------------------------------------------
                     * 1. Get lawyer service region
                     * ------------------------------------------------------
                     */
                    $lawyerServiceRegion =
                        LawyerServiceRegion::query()
                            ->with([
                                'lawyerProfile',
                                'service',
                                'country',
                                'region',
                            ])
                            ->where(
                                'id',
                                $validated[
                                    'lawyer_service_region_id'
                                ]
                            )
                            ->where(
                                'status',
                                'active'
                            )
                            ->first();

                    if (! $lawyerServiceRegion) {

                        throw new \RuntimeException(
                            'Selected lawyer service is not available.'
                        );
                    }

                    /*
                     * ------------------------------------------------------
                     * 2. Make sure selected pricing belongs
                     *    to selected lawyer service region.
                     * ------------------------------------------------------
                     */
                    $pricing = LawyerServicePricing::query()
                        ->where(
                            'id',
                            $validated[
                                'lawyer_service_pricing_id'
                            ]
                        )
                        ->where(
                            'lawyer_service_region_id',
                            $lawyerServiceRegion->id
                        )
                        ->where(
                            'service_level',
                            $validated['service_level']
                        )
                        ->where(
                            'status',
                            'active'
                        )
                        ->first();

                    if (! $pricing) {

                        throw new \RuntimeException(
                            'Selected lawyer pricing is not available.'
                        );
                    }

                    /*
                     * ------------------------------------------------------
                     * 3. Make sure lawyer profile is approved
                     * ------------------------------------------------------
                     */
                    $lawyer = $lawyerServiceRegion->lawyerProfile;

                    if (! $lawyer) {

                        throw new \RuntimeException(
                            'Lawyer profile not found.'
                        );
                    }

                    if (
                        $lawyer->approval_status !== 'approved'
                        || ! $lawyer->is_available
                    ) {

                        throw new \RuntimeException(
                            'Selected lawyer is currently unavailable.'
                        );
                    }

                    /*
                     * ------------------------------------------------------
                     * 4. Get delivery rate
                     * ------------------------------------------------------
                     */
                    $deliveryRate =
                        DeliveryMethodRate::query()
                            ->where(
                                'delivery_method_id',
                                $validated[
                                    'delivery_method_id'
                                ]
                            )
                            ->where(
                                'country_id',
                                $lawyerServiceRegion->country_id
                            )
                            ->where(
                                'status',
                                'active'
                            )
                            ->first();

                    if (! $deliveryRate) {

                        throw new \RuntimeException(
                            'Delivery is not available for the selected country.'
                        );
                    }

                    /*
                     * ------------------------------------------------------
                     * 5. Check delivery method itself
                     * ------------------------------------------------------
                     */
                    if (
                        ! $deliveryRate->deliveryMethod
                    ) {

                        throw new \RuntimeException(
                            'Selected delivery method is unavailable.'
                        );
                    }

                    if (
                        $deliveryRate->deliveryMethod->status
                        !== 'active'
                    ) {

                        throw new \RuntimeException(
                            'Selected delivery method is inactive.'
                        );
                    }

                    /*
                     * ------------------------------------------------------
                     * 6. Calculate prices
                     * ------------------------------------------------------
                     */

                    $serviceFee = (float) $pricing->fee;

                    $deliveryFee = (float) $deliveryRate->price;

                    $additionalFee = 0;

                    $discountAmount = 0;

                    $taxAmount = 0;

                    $serviceFeeTotal = $serviceFee;

                    $subtotal =
                        $serviceFeeTotal
                        + $deliveryFee
                        + $additionalFee;

                    $totalAmount =
                        $subtotal
                        + $taxAmount
                        - $discountAmount;

                    /*
                     * ------------------------------------------------------
                     * 7. Generate order number
                     * ------------------------------------------------------
                     */
                    do {

                        $orderNumber =
                            'ORD-' .
                            now()->format('Ymd') .
                            '-' .
                            strtoupper(
                                Str::random(6)
                            );

                    } while (
                        Order::where(
                            'order_number',
                            $orderNumber
                        )->exists()
                    );

                    /*
                     * ------------------------------------------------------
                     * 8. Create order
                     * ------------------------------------------------------
                     */
                    return Order::create([

                        'customer_id'
                            => $customer->id,

                        'lawyer_profile_id'
                            => $lawyerServiceRegion
                                ->lawyer_profile_id,

                        'lawyer_service_region_id'
                            => $lawyerServiceRegion->id,

                        'lawyer_service_pricing_id'
                            => $pricing->id,

                        'service_id'
                            => $lawyerServiceRegion
                                ->service_id,

                        'country_id'
                            => $lawyerServiceRegion
                                ->country_id,

                        'region_id'
                            => $lawyerServiceRegion
                                ->region_id,

                        'assigned_officer_id'
                            => null,

                        'order_number'
                            => $orderNumber,

                        'service_level'
                            => $validated['service_level'],

                        /*
                         * Historical snapshot.
                         */
                        'service_fee'
                            => $serviceFee,

                        'currency'
                            => $pricing->currency,

                        'estimated_processing_days'
                            => $pricing->estimated_days,

                        /*
                         * Financial snapshot.
                         */
                        'service_fee_total'
                            => $serviceFeeTotal,

                        'delivery_fee'
                            => $deliveryFee,

                        'additional_fee'
                            => $additionalFee,

                        'tax_amount'
                            => $taxAmount,

                        'discount_amount'
                            => $discountAmount,

                        'subtotal'
                            => $subtotal,

                        'total_amount'
                            => $totalAmount,

                        /*
                         * Initial order state.
                         */
                        'status'
                            => 'pending_payment',

                        'payment_status'
                            => 'unpaid',

                        'customer_notes'
                            => $validated['customer_notes']
                            ?? null,
                    ]);
                }
            );

            /*
             * Load complete response.
             */
            $order->load([
                'lawyer.user:id,name,email',
                'service:id,name,slug',
                'country:id,name,code',
                'region:id,name',
                'lawyerServicePricing:id,lawyer_service_region_id,service_level,fee,currency,estimated_days',
                'deliveryMethod:id,name,slug,type',
            ]);

            return $this->response->created(
                $order,
                'Order created successfully. Please complete payment.'
            );

        } catch (\RuntimeException $e) {

            return $this->response->error(
                $e->getMessage(),
                422
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to create order.',
                500
            );
        }
    }

    /**
     * Show customer's order.
     */
    public function show(
        Request $request,
        Order $order
    ): JsonResponse {
        try {

            /*
             * Customer can only see own order.
             */
            if (
                $order->customer_id
                !== $request->user()->id
            ) {

                return $this->response->error(
                    'You are not authorized to view this order.',
                    403
                );
            }

            $order->load([
                'lawyer.user:id,name,email,phone',
                'service:id,name,slug,description',
                'country:id,name,code',
                'region:id,name',
                'lawyerServicePricing',
                'lawyerServiceRegion',
                'deliveryMethod',
                'documents',
                'documentRequests',
                'delivery',
                'invoices',
                'payments',
                'certificates',
                'statusHistories',
            ]);

            return $this->response->success(
                $order,
                'Order retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to retrieve order.',
                500
            );
        }
    }

    /**
     * Cancel order.
     */
    public function cancel(
        Request $request,
        Order $order
    ): JsonResponse {
        try {

            if (
                $order->customer_id
                !== $request->user()->id
            ) {

                return $this->response->error(
                    'You are not authorized to cancel this order.',
                    403
                );
            }

            /*
             * Only early-stage orders can be cancelled
             * by customer.
             */
            $allowedStatuses = [
                'draft',
                'pending_payment',
                'documents_pending',
            ];

            if (
                ! in_array(
                    $order->status,
                    $allowedStatuses,
                    true
                )
            ) {

                return $this->response->error(
                    'This order can no longer be cancelled by the customer.',
                    409
                );
            }

            DB::transaction(function () use ($order) {

                $order->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

                $order->statusHistories()->create([
                    'status' => 'cancelled',
                    'changed_by' => auth()->id(),
                    'notes' => 'Order cancelled by customer.',
                ]);
            });

            return $this->response->success(
                $order->fresh(),
                'Order cancelled successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return $this->response->error(
                'Failed to cancel order.',
                500
            );
        }
    }
}