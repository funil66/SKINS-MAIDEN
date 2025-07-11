<?php

namespace App\Services\Privacy;

use App\Models\User;
use App\Models\DataSubjectRequest;
use Illuminate\Support\Facades\DB;

class LGPDComplianceService
{
    /**
     * Processa uma solicitação de apagamento de dados.
     */
    public function processErasureRequest(DataSubjectRequest $request): bool
    {
        try {
            DB::beginTransaction();
            
            $user = $request->user;
            
            // Anonimizar dados em vez de deletar completamente
            $user->update([
                'name' => 'Usuário Removido',
                'email' => 'removed_' . $user->id . '@privacy.local',
                'password' => null,
                'steam_id' => null,
                'avatar' => null,
            ]);
            
            // Marcar outros dados como removidos
            $user->payments()->update(['metadata' => ['user_data_removed' => true]]);
            $user->contracts()->update(['buyer_email' => 'privacy@removed.local']);
            $user->messages()->update(['content' => '[Mensagem removida por solicitação LGPD]']);
            
            // Atualizar status da solicitação
            $request->update([
                'status' => 'completed',
                'response' => 'Dados do usuário foram anonimizados conforme LGPD.',
                'responded_at' => now(),
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    /**
     * Gera relatório de dados processados.
     */
    public function generateDataProcessingReport(): array
    {
        return [
            'total_users' => User::count(),
            'active_requests' => DataSubjectRequest::whereIn('status', ['pending', 'in_progress'])->count(),
            'completed_requests' => DataSubjectRequest::where('status', 'completed')->count(),
            'data_categories' => [
                'identification' => 'Nome, email, ID Steam',
                'transactional' => 'Histórico de contratos e pagamentos',
                'behavioral' => 'Avaliações e reputação',
                'communication' => 'Mensagens no chat',
            ],
            'legal_basis' => [
                'contract_performance' => 'Execução de contratos de compra/venda',
                'legitimate_interest' => 'Prevenção de fraudes e segurança',
                'consent' => 'Marketing e comunicações opcionais',
            ],
            'retention_periods' => [
                'user_data' => '5 anos após último acesso',
                'transaction_data' => '10 anos (obrigação legal)',
                'communication_data' => '2 anos',
            ],
        ];
    }
}
