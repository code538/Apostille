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
        Schema::create('lawyer_documents', function (Blueprint $table) {
            $table->id();

            /*
             * Lawyer profile.
             */
            $table->foreignId('lawyer_profile_id')
                ->constrained('lawyer_profiles')
                ->cascadeOnDelete();

            /*
             * Type of professional document.
             *
             * Examples:
             *
             * bar_certificate
             * practising_certificate
             * professional_license
             * government_id
             * passport
             * proof_of_address
             * law_degree
             * law_firm_registration
             * other
             */
            $table->enum('document_type', [
                'bar_certificate',
                'practising_certificate',
                'professional_license',
                'government_id',
                'passport',
                'proof_of_address',
                'law_degree',
                'law_firm_registration',
                'other',
            ]);

            /*
             * Optional document/reference number.
             */
            $table->string('document_number', 150)->nullable();

            /*
             * Uploaded document storage path.
             *
             * Example:
             * lawyer-documents/15/bar_certificate/abc.pdf
             */
            $table->string('file_path', 500);

            /*
             * Original uploaded filename.
             */
            $table->string('file_name', 255);

            /*
             * MIME type.
             *
             * Example:
             * application/pdf
             * image/jpeg
             */
            $table->string('mime_type', 100)->nullable();

            /*
             * File size in bytes.
             */
            $table->unsignedBigInteger('file_size')->nullable();

            /*
             * Document verification workflow.
             */
            $table->enum('verification_status', [
                'pending',
                'under_review',
                'verified',
                'rejected',
            ])->default('pending');

            /*
             * Admin/Apostille Officer/authorized staff
             * who verified the document.
             */
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();

            /*
             * Reason when document is rejected.
             */
            $table->text('rejection_reason')->nullable();

            /*
             * Document expiry date.
             *
             * Useful for practising certificates,
             * professional licences, etc.
             */
            $table->date('expires_at')->nullable();

            /*
             * Optional notes from reviewer.
             */
            $table->text('reviewer_notes')->nullable();

            $table->timestamps();

            /*
             * Indexes.
             */
            $table->index('document_type');
            $table->index('verification_status');
            $table->index('expires_at');

            /*
             * One lawyer may upload multiple documents
             * of the same type, for example old/new certificates.
             */
            $table->index([
                'lawyer_profile_id',
                'document_type',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawyer_documents');
    }
};
