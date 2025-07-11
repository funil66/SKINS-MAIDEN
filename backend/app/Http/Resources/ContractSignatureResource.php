<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractSignatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contract_id' => $this->contract_id,
            'signer_id' => $this->signer_id,
            'signer_type' => $this->signer_type,
            'signed_at' => $this->signed_at->toISOString(),
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'is_valid' => $this->invalidated_at === null,
            'invalidated_at' => $this->invalidated_at?->toISOString(),
            'invalidation_reason' => $this->invalidation_reason,
            'created_at' => $this->created_at->toISOString(),
            
            // Dados sensíveis apenas para admins
            'signature_hash' => $this->when(
                $request->user()?->hasRole('admin'),
                $this->signature_hash
            ),
            
            'content_hash' => $this->when(
                $request->user()?->hasRole('admin'),
                $this->content_hash
            ),
            
            'metadata' => $this->when(
                $request->user()?->hasRole('admin') || $request->has('include_metadata'),
                $this->metadata
            ),
            
            // Relacionamentos
            'signer' => $this->whenLoaded('signer', function () {
                return [
                    'id' => $this->signer->id,
                    'name' => $this->signer->name,
                    'email' => $this->signer->email
                ];
            }),
            
            'invalidator' => $this->whenLoaded('invalidator', function () {
                return [
                    'id' => $this->invalidator->id,
                    'name' => $this->invalidator->name
                ];
            }),
            
            // Campos computados
            'signer_type_display' => $this->getSignerTypeDisplay(),
            'status' => $this->invalidated_at ? 'invalidated' : 'valid',
            'status_display' => $this->invalidated_at ? 'Invalidada' : 'Válida'
        ];
    }
    
    /**
     * Obter descrição do tipo de assinante
     */
    private function getSignerTypeDisplay(): string
    {
        return match ($this->signer_type) {
            'buyer' => 'Comprador',
            'seller' => 'Vendedor',
            'witness' => 'Testemunha',
            'admin' => 'Administrador',
            default => 'Tipo Desconhecido'
        };
    }
}
