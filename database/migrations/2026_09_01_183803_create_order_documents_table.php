<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('previous_document_id')
                ->nullable()
                ->constrained('order_documents')
                ->nullOnDelete();

            $table->string('document_type', 100);
            $table->string('title');

            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->unsignedInteger('version')->default(1);

            $table->enum('status', [
                'uploaded',
                'under_review',
                'accepted',
                'rejected',
                'replacement_required',
            ])->default('uploaded');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['order_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_documents');
    }
};
