<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->enum('status', [
                'pending', 
                'under_review', 
                'requires_more_info', 
                'approved', 
                'rejected', 
                'expired'
            ])->default('pending');
            $table->enum('verification_level', ['basic', 'enhanced', 'premium'])->default('basic');
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('required_documents')->nullable(); // Lista de documentos necessários
            $table->json('additional_data')->nullable(); // Dados extras coletados
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->index(['status']);
            $table->index(['verification_level']);
            $table->index(['submitted_at']);
            $table->index(['expires_at']);
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_requests');
    }
};
