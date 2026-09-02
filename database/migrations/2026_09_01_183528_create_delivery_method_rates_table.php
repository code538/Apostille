<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_method_rates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('delivery_method_id')
                ->constrained('delivery_methods')
                ->cascadeOnDelete();

            $table->foreignId('country_id')
                ->constrained('countries')
                ->restrictOnDelete();

            $table->decimal('price', 12, 2);
            $table->string('currency', 10)->default('GBP');
            $table->unsignedInteger('estimated_days')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(
                ['delivery_method_id', 'country_id'],
                'delivery_country_rate_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_method_rates');
    }
};
