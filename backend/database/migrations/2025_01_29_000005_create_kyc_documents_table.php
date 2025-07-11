<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('kyc_request_id');
            $table->enum('document_type', [
                'cpf',
                'rg', 
                'cnh',
                'passport',
                'selfie',
                'selfie_with_document',
                'address_proof',
                'income_proof',
                'bank_statement'
            ]);
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path', 500);
            $table->string('file_hash', 64);
            $table->string('mime_type', 100);
            $table->integer('file_size');
            $table->boolean('verified')->default(false);
            $table->text('verification_notes')->nullable();
            $table->json('ocr_data')->nullable(); // Dados extraídos por OCR
            $table->json('analysis_result')->nullable(); // Resultado da análise automatizada
            $table->timestamp('uploaded_at');
            $table->timestamp('verified_at')->nullable();
            $table->uuid('verified_by')->nullable();
            $table->timestamps();
            
            $table->index(['kyc_request_id']);
            $table->index(['document_type']);
            $table->index(['verified']);
            $table->index(['uploaded_at']);
            
            $table->foreign('kyc_request_id')->references('id')->on('kyc_requests')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_documents');
    }
};
