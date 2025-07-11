<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_id' => $this->transaction_id,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'template_id' => $this->template_id,
            'status' => $this->status,
            'signed_at' => $this->signed_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Campos condicionais baseados no contexto
            'content' => $this->when(
                $request->routeIs('contracts.show') || $request->has('include_content'),
                $this->content
            ),
            
            'metadata' => $this->when(
                $request->user()?->hasRole('admin') || $request->has('include_metadata'),
                $this->metadata
            ),
            
            // Relacionamentos
            'template' => $this->whenLoaded('template', function () {
                return [
                    'id' => $this->template->id,
                    'name' => $this->template->name,
                    'category' => $this->template->category,
                    'version' => $this->template->version
                ];
            }),
            
            'buyer' => $this->whenLoaded('buyer', function () {
                return [
                    'id' => $this->buyer->id,
                    'name' => $this->buyer->name,
                    'email' => $this->buyer->email
                ];
            }),
            
            'seller' => $this->whenLoaded('seller', function () {
                return [
                    'id' => $this->seller->id,
                    'name' => $this->seller->name,
                    'email' => $this->seller->email
                ];
            }),
            
            'signatures' => $this->whenLoaded('signatures', function () {
                return ContractSignatureResource::collection($this->signatures);
            }),
            
            // Campos computados
            'is_signed' => $this->status === 'signed',
            'is_expired' => $this->expires_at && $this->expires_at->isPast(),
            'signature_count' => $this->whenCounted('signatures'),
            
            // Status display
            'status_display' => $this->getStatusDisplay(),
            
            // Actions disponíveis para o usuário atual
            'available_actions' => $this->getAvailableActions($request->user())
        ];
    }
    
    /**
     * Obter descrição do status
     */
    private function getStatusDisplay(): string
    {
        return match ($this->status) {
            'draft' => 'Rascunho',
            'pending_signature' => 'Aguardando Assinatura',
            'partially_signed' => 'Parcialmente Assinado',
            'signed' => 'Assinado',
            'cancelled' => 'Cancelado',
            'expired' => 'Expirado',
            default => 'Status Desconhecido'
        };
    }
    
    /**
     * Obter ações disponíveis para o usuário
     */
    private function getAvailableActions($user): array
    {
        if (!$user) {
            return [];
        }
        
        $actions = [];
        
        // Ações baseadas no status
        switch ($this->status) {
            case 'draft':
                $actions[] = 'edit';
                $actions[] = 'finalize';
                $actions[] = 'delete';
                break;
                
            case 'pending_signature':
            case 'partially_signed':
                if ($this->canUserSign($user)) {
                    $actions[] = 'sign';
                }
                $actions[] = 'view_signatures';
                break;
                
            case 'signed':
                $actions[] = 'view_signatures';
                $actions[] = 'download';
                break;
        }
        
        // Ações administrativas
        if ($user->hasRole('admin')) {
            $actions[] = 'view_metadata';
            $actions[] = 'invalidate_signatures';
        }
        
        return array_unique($actions);
    }
    
    /**
     * Verificar se usuário pode assinar
     */
    private function canUserSign($user): bool
    {
        // Comprador ou vendedor podem assinar
        if ($this->buyer_id === $user->id || $this->seller_id === $user->id) {
            // Verificar se já assinou
            // Esta verificação seria feita no serviço, aqui é simplificada
            return true;
        }
        
        return false;
    }
}
