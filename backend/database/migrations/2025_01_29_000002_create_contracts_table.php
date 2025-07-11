<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id');
            $table->uuid('buyer_id');
            $table->uuid('seller_id');
            $table->uuid('template_id');
            $table->longText('content');
            $table->enum('status', [
                'draft', 
                'pending_signature', 
                'partially_signed', 
                'signed', 
                'completed', 
                'cancelled'
            ])->default('draft');
            $table->string('hash_sha256', 64);
            $table->json('metadata')->nullable(); // Dados extras do contrato
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['transaction_id']);
            $table->index(['buyer_id']);
            $table->index(['seller_id']);
            $table->index(['status']);
            $table->index(['created_at']);
            
            $table->foreign('template_id')->references('id')->on('contract_templates');
            $table->foreign('buyer_id')->references('id')->on('users');
            $table->foreign('seller_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
