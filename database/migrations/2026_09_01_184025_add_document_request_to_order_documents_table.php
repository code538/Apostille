<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_documents', function (Blueprint $table) {
            $table->foreignId('document_request_id')
                ->nullable()
                ->after('uploaded_by')
                ->constrained('document_requests')
                ->nullOnDelete();

            $table->index('document_request_id', 'order_docs_request_idx');
        });
    }

    public function down(): void
    {
        Schema::table('order_documents', function (Blueprint $table) {
            $table->dropForeign(['document_request_id']);
            $table->dropIndex('order_docs_request_idx');
            $table->dropColumn('document_request_id');
        });
    }
};
