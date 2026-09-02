<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {

            $table->id();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('lawyer_profile_id')
                ->constrained('lawyer_profiles')
                ->restrictOnDelete();

            $table->foreignId('lawyer_service_region_id')
                ->constrained('lawyer_service_regions')
                ->restrictOnDelete();

            $table->foreignId('lawyer_service_pricing_id')
                ->constrained('lawyer_service_pricings')
                ->restrictOnDelete();

            $table->foreignId('service_id')
                ->constrained('services')
                ->restrictOnDelete();

            $table->foreignId('country_id')
                ->constrained('countries')
                ->restrictOnDelete();

            $table->foreignId('region_id')
                ->nullable()
                ->constrained('regions')
                ->nullOnDelete();

            // Internal officer assigned to process this order.
            $table->foreignId('assigned_officer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('order_number', 50)->unique();

            $table->enum('service_level', [
                'standard',
                'express',
                'urgent',
            ]);

            // Historical pricing snapshot.
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->string('currency', 10)->default('GBP');
            $table->unsignedInteger('estimated_processing_days')->nullable();

            $table->enum('status', [
                'draft',
                'pending_payment',
                'documents_pending',
                'under_review',
                'additional_documents_required',
                'processing',
                'submitted_to_government',
                'government_processing',
                'certificate_ready',
                'delivery_pending',
                'completed',
                'on_hold',
                'cancelled',
                'rejected',
            ])->default('draft');

            $table->enum('payment_status', [
                'unpaid',
                'partially_paid',
                'paid',
                'refunded',
                'partially_refunded',
            ])->default('unpaid');

            $table->decimal('service_fee_total', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('additional_fee', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['lawyer_profile_id', 'status']);
            $table->index(
                ['service_id', 'country_id', 'region_id'],
                'order_service_location_idx'
            );
            $table->index(['assigned_officer_id', 'status']);
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
