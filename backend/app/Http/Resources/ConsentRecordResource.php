<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsentRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'purpose' => $this->purpose,
            'legal_basis' => $this->legalBasis,
            'data_types' => $this->dataTypes,
            'granted' => $this->granted,
            'granted_at' => $this->grantedAt?->toISOString(),
            'revoked_at' => $this->revokedAt?->toISOString(),
            'expires_at' => $this->expiresAt?->toISOString(),
            'created_at' => $this->createdAt->toISOString(),
            
            // Campos computados
            'purpose_display' => $this->getPurposeDisplay(),
            'legal_basis_display' => $this->getLegalBasisDisplay(),
            'is_active' => $this->getIsActive(),
            'is_expired' => $this->getIsExpired(),
            'status' => $this->getStatus(),
            
            // Dados sensíveis apenas para admins ou próprio usuário
            'ip_address' => $this->when(
                $request->user()?->hasRole('admin') || $request->user()?->id === $this->userId,
                $this->ipAddress
            ),
            
            'metadata' => $this->when(
                $request->user()?->hasRole('admin'),
                $this->metadata
            )
        ];
    }
    
    private function getPurposeDisplay(): string
    {
        return match ($this->purpose) {
            'essential' => 'Funcionalidades Essenciais',
            'marketing' => 'Marketing e Comunicação',
            'analytics' => 'Análise e Estatísticas',
            'personalization' => 'Personalização',
            'third_party' => 'Compartilhamento com Terceiros',
            default => 'Finalidade Personalizada'
        };
    }
    
    private function getLegalBasisDisplay(): string
    {
        return match ($this->legalBasis) {
            'consent' => 'Consentimento',
            'contract' => 'Execução de Contrato',
            'legal_obligation' => 'Obrigação Legal',
            'vital_interests' => 'Interesses Vitais',
            'public_task' => 'Tarefa Pública',
            'legitimate_interests' => 'Interesses Legítimos',
            default => 'Base Legal Desconhecida'
        };
    }
    
    private function getIsActive(): bool
    {
        return $this->granted && 
               !$this->revokedAt && 
               (!$this->expiresAt || $this->expiresAt->isFuture());
    }
    
    private function getIsExpired(): bool
    {
        return $this->expiresAt && $this->expiresAt->isPast();
    }
    
    private function getStatus(): string
    {
        if ($this->revokedAt) {
            return 'revoked';
        }
        
        if ($this->expiresAt && $this->expiresAt->isPast()) {
            return 'expired';
        }
        
        if ($this->granted) {
            return 'active';
        }
        
        return 'denied';
    }
}
