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
       Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->unique(['slug']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
