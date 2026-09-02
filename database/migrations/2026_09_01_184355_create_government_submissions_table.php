<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('government_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('submitted_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('government_department')->nullable();
            $table->string('portal_name')->nullable();
            $table->string('application_reference')->nullable();

            $table->enum('status', [
                'draft',
                'ready',
                'submitted',
                'processing',
                'approved',
                'rejected',
                'completed',
            ])->default('draft');

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();

            $table->text('submission_notes')->nullable();
            $table->text('response_notes')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('application_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('government_submissions');
    }
};
