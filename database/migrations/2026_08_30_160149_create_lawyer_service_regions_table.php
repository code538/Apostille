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
        Schema::create('lawyer_service_regions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lawyer_profile_id')
                ->constrained('lawyer_profiles')
                ->cascadeOnDelete();

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

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->timestamps();

            $table->index(
                [
                    'service_id',
                    'country_id',
                    'region_id',
                    'status',
                ],
                'lsr_service_country_region_status_idx'
            );

            $table->index(
                [
                    'lawyer_profile_id',
                    'service_id',
                ],
                'lsr_lawyer_service_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawyer_service_regions');
    }
};
