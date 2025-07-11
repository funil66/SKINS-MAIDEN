<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->enum('request_type', [
                'access',        // Art. 15 - Direito de acesso
                'rectification', // Art. 16 - Direito de retificação
                'erasure',       // Art. 17 - Direito ao esquecimento
                'restriction',   // Art. 18 - Direito à limitação
                'portability',   // Art. 20 - Direito à portabilidade
                'objection',     // Art. 21 - Direito de oposição
                'automated_decision' // Art. 22 - Decisões automatizadas
            ]);
            $table->enum('status', [
                'pending',
                'processing', 
                'completed',
                'rejected',
                'cancelled'
            ])->default('pending');
            $table->text('description')->nullable(); // Descrição detalhada da solicitação
            $table->json('specific_data')->nullable(); // Dados específicos solicitados
            $table->text('rejection_reason')->nullable();
            $table->uuid('assigned_to')->nullable(); // Responsável pelo atendimento
            $table->timestamp('requested_at');
            $table->timestamp('deadline_at'); // Prazo legal para resposta (72h)
            $table->timestamp('completed_at')->nullable();
            $table->json('response_data')->nullable(); // Dados da resposta
            $table->string('response_file_path')->nullable(); // Arquivo de resposta
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->index(['request_type']);
            $table->index(['status']);
            $table->index(['requested_at']);
            $table->index(['deadline_at']);
            $table->index(['assigned_to']);
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privacy_requests');
    }
};
