<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('transaction_id')->nullable()->unique();
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
                'partially_refunded',
            ])->default('pending');

            $table->timestamp('paid_at')->nullable();
            $table->json('gateway_response')->nullable();

            $table->timestamps();

            $table->index(['invoice_id', 'status']);
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
