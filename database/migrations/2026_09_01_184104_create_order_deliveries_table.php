<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('delivery_method_id')
                ->constrained('delivery_methods')
                ->restrictOnDelete();

            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();

            $table->string('recipient_name');
            $table->string('phone', 30)->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();

            // Historical delivery price snapshot.
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->string('currency', 10)->default('GBP');
            $table->unsignedInteger('estimated_days')->nullable();

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

            $table->index(['order_id', 'status']);
            $table->index('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_deliveries');
    }
};
