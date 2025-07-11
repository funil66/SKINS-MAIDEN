<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('kyc_request_id');
            $table->uuid('reviewer_id');
            $table->enum('decision', ['approved', 'rejected', 'requires_more_info']);
            $table->text('notes')->nullable();
            $table->json('checklist_results')->nullable(); // Resultado do checklist de verificação
            $table->json('document_analysis')->nullable(); // Análise detalhada dos documentos
            $table->timestamp('reviewed_at');
            $table->timestamps();
            
            $table->index(['kyc_request_id']);
            $table->index(['reviewer_id']);
            $table->index(['decision']);
            $table->index(['reviewed_at']);
            
            $table->foreign('kyc_request_id')->references('id')->on('kyc_requests')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_reviews');
    }
};
