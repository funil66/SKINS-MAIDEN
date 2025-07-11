<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataProcessingLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'activity_type' => $this->activityType,
            'data_category' => $this->dataCategory,
            'legal_basis' => $this->legalBasis,
            'purpose' => $this->purpose,
            'data_elements' => $this->dataElements,
            'processing_location' => $this->processingLocation,
            'third_parties' => $this->thirdParties,
            'retention_period' => $this->retentionPeriod,
            'automated_decision' => $this->automatedDecision,
            'consent_reference' => $this->consentReference,
            'processed_at' => $this->processedAt->toISOString(),
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            
            // Campos computados
            'activity_type_display' => $this->getActivityTypeDisplay(),
            'legal_basis_display' => $this->getLegalBasisDisplay(),
            'data_category_display' => $this->getDataCategoryDisplay(),
            'retention_status' => $this->getRetentionStatus(),
            
            // Relacionamentos
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ];
            }),
            
            'consent' => $this->whenLoaded('consent', function () {
                return [
                    'id' => $this->consent->id,
                    'status' => $this->consent->status,
                    'granted_at' => $this->consent->grantedAt->toISOString()
                ];
            })
        ];
    }
    
    private function getActivityTypeDisplay(): string
    {
        return match ($this->activityType) {
            'create' => 'Criação',
            'read' => 'Leitura',
            'update' => 'Atualização',
            'delete' => 'Exclusão',
            'export' => 'Exportação',
            'share' => 'Compartilhamento',
            'anonymize' => 'Anonimização',
            'backup' => 'Backup',
            'restore' => 'Restauração',
            default => 'Tipo Desconhecido'
        };
    }
    
    private function getLegalBasisDisplay(): string
    {
        return match ($this->legalBasis) {
            'consent' => 'Consentimento',
            'contract' => 'Contrato',
            'legal_obligation' => 'Obrigação Legal',
            'vital_interests' => 'Interesses Vitais',
            'public_task' => 'Tarefa de Interesse Público',
            'legitimate_interests' => 'Interesses Legítimos',
            default => 'Base Legal Desconhecida'
        ];
    }
    
    private function getDataCategoryDisplay(): string
    {
        return match ($this->dataCategory) {
            'personal_data' => 'Dados Pessoais',
            'sensitive_data' => 'Dados Sensíveis',
            'financial_data' => 'Dados Financeiros',
            'biometric_data' => 'Dados Biométricos',
            'location_data' => 'Dados de Localização',
            'behavioral_data' => 'Dados Comportamentais',
            'transaction_data' => 'Dados de Transação',
            'communication_data' => 'Dados de Comunicação',
            default => 'Categoria Desconhecida'
        ];
    }
    
    private function getRetentionStatus(): string
    {
        if (!$this->retentionPeriod) {
            return 'Indefinido';
        }
        
        $retentionDate = $this->processedAt->addMonths($this->retentionPeriod);
        
        if ($retentionDate->isPast()) {
            return 'Expirado';
        }
        
        if ($retentionDate->diffInDays() <= 30) {
            return 'Próximo ao Vencimento';
        }
        
        return 'Válido';
    }
}
}
