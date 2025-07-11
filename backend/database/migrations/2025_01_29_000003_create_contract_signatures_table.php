<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_signatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contract_id');
            $table->uuid('user_id');
            $table->string('signature_hash', 64); // Hash da assinatura
            $table->string('signature_type', 20)->default('digital'); // digital, electronic, wet
            $table->text('signature_data')->nullable(); // Dados da assinatura (base64, coordinates, etc)
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->json('verification_data')->nullable(); // Dados de verificação adicional
            $table->timestamp('signed_at');
            $table->timestamps();
            
            $table->index(['contract_id']);
            $table->index(['user_id']);
            $table->index(['signed_at']);
            
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            
            // Garantir que cada usuário assine apenas uma vez por contrato
            $table->unique(['contract_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_signatures');
    }
};
