<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */
            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Lawyer
            |--------------------------------------------------------------------------
            */
            $table->foreignId('lawyer_profile_id')
                ->constrained('lawyer_profiles')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Service
            |--------------------------------------------------------------------------
            */
            $table->foreignId('service_id')
                ->constrained('services')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Country / Region
            |--------------------------------------------------------------------------
            */
            $table->foreignId('country_id')
                ->constrained('countries')
                ->restrictOnDelete();

            $table->foreignId('region_id')
                ->nullable()
                ->constrained('regions')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Order Number
            |--------------------------------------------------------------------------
            */
            $table->string('order_number', 50)
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | Order Status
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'draft',
                'pending_payment',
                'payment_paid',
                'documents_pending',
                'under_review',
                'additional_documents_required',
                'processing',
                'submitted_to_government',
                'government_processing',
                'certificate_ready',
                'delivery_pending',
                'completed',
                'cancelled',
                'rejected',
            ])->default('draft');

            /*
            |--------------------------------------------------------------------------
            | Payment Status
            |--------------------------------------------------------------------------
            */
            $table->enum('payment_status', [
                'unpaid',
                'partially_paid',
                'paid',
                'refunded',
                'partially_refunded',
            ])->default('unpaid');

            /*
            |--------------------------------------------------------------------------
            | Pricing Snapshot
            |--------------------------------------------------------------------------
            */
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Important Dates
            |--------------------------------------------------------------------------
            */
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->timestamps();

            $table->index([
                'customer_id',
                'status',
            ]);

            $table->index([
                'lawyer_profile_id',
                'status',
            ]);

            $table->index([
                'service_id',
                'country_id',
                'region_id',
            ]);

            $table->index('payment_status');
        });


        Schema::create('delivery_methods', function (Blueprint $table) {

            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->text('description')->nullable();

            $table->enum('type', [
                'digital',
                'courier',
                'postal',
                'pickup',
            ])->default('digital');

            $table->decimal('price', 12, 2)->default(0);

            $table->integer('estimated_days')->nullable();

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->timestamps();
        });

        Schema::create('order_deliveries', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('delivery_method_id')
                ->constrained('delivery_methods')
                ->restrictOnDelete();

            $table->string('recipient_name');
            $table->string('phone', 30)->nullable();

            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();

            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();

            $table->string('tracking_number')->nullable();

            $table->enum('status', [
                'pending',
                'ready',
                'assigned',
                'picked_up',
                'in_transit',
                'delivered',
                'failed',
                'cancelled',
            ])->default('pending');

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            $table->index([
                'order_id',
                'status',
            ]);

            $table->index('tracking_number');
        });


        Schema::create('order_documents', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Who uploaded it?
            |--------------------------------------------------------------------------
            */
            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Document information
            |--------------------------------------------------------------------------
            */
            $table->string('document_type', 100);

            $table->string('title');

            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Version
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('version')->default(1);

            /*
            |--------------------------------------------------------------------------
            | Document status
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'uploaded',
                'under_review',
                'accepted',
                'rejected',
                'replacement_required',
            ])->default('uploaded');

            /*
            |--------------------------------------------------------------------------
            | Lawyer Review
            |--------------------------------------------------------------------------
            */
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->text('reviewer_notes')->nullable();

            $table->timestamps();

            $table->index([
                'order_id',
                'status',
            ]);

            $table->index([
                'order_id',
                'document_type',
            ]);
        });


        Schema::create('document_requests', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Requested by lawyer/officer
            |--------------------------------------------------------------------------
            */
            $table->foreignId('requested_by')
                ->constrained('users')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Document requested
            |--------------------------------------------------------------------------
            */
            $table->string('document_type', 100);

            $table->string('title');

            $table->text('description')->nullable();

            $table->enum('status', [
                'pending',
                'uploaded',
                'accepted',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->timestamp('requested_at')->useCurrent();

            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index([
                'order_id',
                'status',
            ]);
        });


        Schema::create('invoices', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('invoice_number', 50)
                ->unique();

            $table->enum('status', [
                'draft',
                'issued',
                'partially_paid',
                'paid',
                'cancelled',
                'refunded',
            ])->default('draft');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);

            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index([
                'customer_id',
                'status',
            ]);
        });


        Schema::create('invoice_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->string('item_type', 50);

            $table->string('description');

            $table->unsignedInteger('quantity')->default(1);

            $table->decimal('unit_price', 12, 2);

            $table->decimal('total_price', 12, 2);

            /*
            | Optional references
            */
            $table->foreignId('service_id')
                ->nullable()
                ->constrained('services')
                ->nullOnDelete();

            $table->foreignId('delivery_method_id')
                ->nullable()
                ->constrained('delivery_methods')
                ->nullOnDelete();

            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('transaction_id')
                ->nullable()
                ->unique();

            $table->string('payment_gateway', 50)->nullable();

            $table->string('payment_method', 50)->nullable();

            $table->decimal('amount', 12, 2);

            $table->string('currency', 10)->default('GBP');

            $table->enum('status', [
                'pending',
                'processing',
                'successful',
                'failed',
                'cancelled',
                'refunded',
            ])->default('pending');

            $table->timestamp('paid_at')->nullable();

            $table->json('gateway_response')->nullable();

            $table->timestamps();

            $table->index([
                'invoice_id',
                'status',
            ]);
        });

        Schema::create('government_submissions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('submitted_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('government_department')->nullable();

            $table->string('portal_name')->nullable();

            $table->string('application_reference')->nullable();

            $table->enum('status', [
                'draft',
                'ready',
                'submitted',
                'processing',
                'approved',
                'rejected',
                'completed',
            ])->default('draft');

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'order_id',
                'status',
            ]);

            $table->index('application_reference');
        });


        Schema::create('order_certificates', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('certificate_type');

            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->string('certificate_number')->nullable();

            $table->timestamp('received_at')->nullable();

            $table->enum('status', [
                'received',
                'verified',
                'ready_for_delivery',
                'delivered',
            ])->default('received');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'order_id',
                'status',
            ]);
        });


        Schema::create('order_status_histories', function (Blueprint $table) {

            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('from_status', 50)->nullable();

            $table->string('to_status', 50);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index([
                'order_id',
                'created_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('delivery_methods');
        Schema::dropIfExists('order_deliveries');
        Schema::dropIfExists('order_documents');
        Schema::dropIfExists('document_requests');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('government_submissions');
        Schema::dropIfExists('order_certificates');
        Schema::dropIfExists('order_status_histories');
        
    }
};
