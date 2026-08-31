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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            $table->string('name', 150);

            /*
             * ISO 3166-1 alpha-2
             *
             * Examples:
             * GB = United Kingdom
             * IN = India
             * US = United States
             * BD = Bangladesh
             * AE = United Arab Emirates
             * AU = Australia
             */
            $table->string('iso2', 2)->unique();

            /*
             * ISO 3166-1 alpha-3
             *
             * Examples:
             * GBR
             * IND
             * USA
             * BGD
             * ARE
             * AUS
             */
            $table->string('iso3', 3)->unique();

            /*
             * International telephone calling code.
             *
             * Examples:
             * +44
             * +91
             * +1
             * +880
             * +971
             * +61
             */
            $table->string('phone_code', 10)->nullable();

            /*
             * Currency.
             *
             * Examples:
             * GBP
             * INR
             * USD
             * BDT
             * AED
             * AUD
             */
            $table->string('currency_code', 3)->nullable();

            /*
             * Country status.
             */
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};