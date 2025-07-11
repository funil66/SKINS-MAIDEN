<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivacyRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'request_type' => $this->requestType,
            'description' => $this->description,
            'status' => $this->status,
            'requested_at' => $this->requestedAt->toISOString(),
            'deadline' => $this->deadline->toISOString(),
            'response' => $this->response,
            'responded_at' => $this->respondedAt?->toISOString(),
            'created_at' => $this->createdAt->toISOString(),
            
            // Campos computados
            'request_type_display' => $this->getRequestTypeDisplay(),
            'status_display' => $this->getStatusDisplay(),
            'is_overdue' => $this->getIsOverdue(),
            'days_remaining' => $this->getDaysRemaining(),
            
            // Relacionamentos
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ];
            }),
            
            'responder' => $this->whenLoaded('responder', function () {
                return [
                    'id' => $this->responder->id,
                    'name' => $this->responder->name
                ];
            }),
            
            // Dados sensíveis apenas para admins
            'attachments' => $this->when(
                $request->user()?->hasRole('admin'),
                $this->attachments
            ),
            
            'metadata' => $this->when(
                $request->user()?->hasRole('admin'),
                $this->metadata
            )
        ];
    }
    
    private function getRequestTypeDisplay(): string
    {
        return match ($this->requestType) {
            'access' => 'Acesso aos Dados',
            'rectification' => 'Retificação de Dados',
            'erasure' => 'Apagamento de Dados',
            'portability' => 'Portabilidade de Dados',
            'restriction' => 'Restrição de Processamento',
            'objection' => 'Oposição ao Processamento',
            default => 'Tipo Desconhecido'
        };
    }
    
    private function getStatusDisplay(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'in_progress' => 'Em Andamento',
            'completed' => 'Concluída',
            'rejected' => 'Rejeitada',
            default => 'Status Desconhecido'
        };
    }
    
    private function getIsOverdue(): bool
    {
        return $this->status === 'pending' && $this->deadline->isPast();
    }
    
    private function getDaysRemaining(): int
    {
        if ($this->status !== 'pending') {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->deadline, false));
    }
}
