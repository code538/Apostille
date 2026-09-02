<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_charges', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('charge_type', 50);
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('GBP');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('status', [
                'pending',
                'invoiced',
                'paid',
                'cancelled',
            ])->default('pending');

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('charge_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_charges');
    }
};
