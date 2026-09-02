<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_certificates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('government_submission_id')
                ->nullable()
                ->constrained('government_submissions')
                ->nullOnDelete();

            $table->string('certificate_type');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('certificate_number')->nullable();

            $table->timestamp('received_at')->nullable();

            $table->enum('status', [
                'received',
                'verified',
                'ready_for_delivery',
                'delivered',
            ])->default('received');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_certificates');
    }
};
