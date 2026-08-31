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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();

            /*
             * Parent country.
             */
            $table->foreignId('country_id')
                ->constrained('countries')
                ->cascadeOnDelete();

            /*
             * Region / State / Province name.
             *
             * Examples:
             *
             * West Bengal
             * Maharashtra
             * Delhi
             * England
             * Scotland
             * California
             * New York
             */
            $table->string('name', 150);

            /*
             * Optional region/state code.
             *
             * Examples:
             * WB
             * MH
             * CA
             * NY
             */
            $table->string('code', 20)->nullable();

            /*
             * Region status.
             */
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            /*
             * A country cannot have two regions
             * with exactly the same name.
             */
            $table->unique([
                'country_id',
                'name',
            ]);

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};