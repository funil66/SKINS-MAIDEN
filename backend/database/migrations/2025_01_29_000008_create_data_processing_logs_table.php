<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_processing_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->enum('action', [
                'create',
                'read', 
                'update',
                'delete',
                'share',
                'export',
                'anonymize',
                'backup',
                'restore'
            ]);
            $table->string('data_type', 100); // Tipo de dado processado
            $table->text('data_description'); // Descrição específica dos dados
            $table->text('purpose'); // Finalidade do processamento
            $table->enum('legal_basis', [
                'consent',
                'contract',
                'legal_obligation',
                'vital_interests',
                'public_task',
                'legitimate_interests'
            ]);
            $table->uuid('processed_by'); // Usuário/sistema que processou
            $table->string('processing_context', 200)->nullable(); // Contexto (web, api, job, etc)
            $table->json('metadata')->nullable(); // Dados adicionais do processamento
            $table->timestamp('processed_at');
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->index(['action']);
            $table->index(['data_type']);
            $table->index(['processed_by']);
            $table->index(['processed_at']);
            $table->index(['legal_basis']);
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_processing_logs');
    }
};
