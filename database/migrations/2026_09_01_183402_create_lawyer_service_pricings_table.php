<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lawyer_service_pricings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lawyer_service_region_id')
                ->constrained('lawyer_service_regions')
                ->cascadeOnDelete();

            $table->enum('service_level', [
                'standard',
                'express',
                'urgent',
            ]);

            $table->decimal('fee', 12, 2);
            $table->string('currency', 10)->default('GBP');
            $table->unsignedInteger('estimated_days')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(
                ['lawyer_service_region_id', 'service_level'],
                'lsr_pricing_level_unique'
            );

            $table->index(['service_level', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyer_service_pricings');
    }
};
