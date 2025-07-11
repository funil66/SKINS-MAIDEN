<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KYCRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'type' => $this->type,
            'status' => $this->status,
            'required_documents' => $this->requiredDocuments,
            'completed_at' => $this->completedAt?->toISOString(),
            'created_at' => $this->createdAt->toISOString(),
            'updated_at' => $this->updatedAt->toISOString(),
            
            // Campos computados
            'status_display' => $this->getStatusDisplay(),
            'type_display' => $this->getTypeDisplay(),
            'is_completed' => in_array($this->status, ['approved', 'rejected']),
            
            // Relacionamentos quando carregados
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ];
            }),
            
            'documents' => $this->whenLoaded('documents', function () {
                return KYCDocumentResource::collection($this->documents);
            }),
            
            'metadata' => $this->when(
                $request->user()?->hasRole('admin'),
                $this->metadata
            )
        ];
    }
    
    private function getStatusDisplay(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'under_review' => 'Em Análise',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            default => 'Status Desconhecido'
        };
    }
    
    private function getTypeDisplay(): string
    {
        return match ($this->type) {
            'basic' => 'Verificação Básica',
            'advanced' => 'Verificação Avançada',
            'business' => 'Verificação Empresarial',
            default => 'Tipo Desconhecido'
        };
    }
}
