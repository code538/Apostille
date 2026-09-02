<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('company_name');
            $table->string('registration_number')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone', 30)->nullable();

            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();

            $table->foreignId('region_id')
                ->nullable()
                ->constrained('regions')
                ->nullOnDelete();

            $table->text('address')->nullable();

            $table->enum('status', [
                'pending',
                'active',
                'inactive',
                'suspended',
            ])->default('pending');

            $table->timestamps();

            $table->index(['country_id', 'region_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
