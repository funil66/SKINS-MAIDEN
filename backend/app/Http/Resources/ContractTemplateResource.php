<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'version' => $this->version,
            'parent_template_id' => $this->parent_template_id,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Conteúdo apenas quando solicitado especificamente
            'content' => $this->when(
                $request->routeIs('contract-templates.show') || $request->has('include_content'),
                $this->content
            ),
            
            // Variáveis do template
            'variables' => $this->when(
                $request->routeIs(['contract-templates.show', 'contract-templates.index']) || $request->has('include_variables'),
                $this->variables
            ),
            
            // Relacionamentos
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name
                ];
            }),
            
            'parent_template' => $this->whenLoaded('parentTemplate', function () {
                return [
                    'id' => $this->parentTemplate->id,
                    'name' => $this->parentTemplate->name,
                    'version' => $this->parentTemplate->version
                ];
            }),
            
            'versions' => $this->whenLoaded('versions', function () {
                return $this->versions->map(function ($version) {
                    return [
                        'id' => $version->id,
                        'version' => $version->version,
                        'created_at' => $version->created_at->toISOString(),
                        'is_active' => $version->is_active
                    ];
                });
            }),
            
            // Estatísticas de uso
            'usage_count' => $this->whenAppended('usage_count'),
            'last_used_at' => $this->when(
                $this->whenAppended('last_used_at'),
                $this->last_used_at?->toISOString()
            ),
            
            // Campos computados
            'category_display' => $this->getCategoryDisplay(),
            'status_display' => $this->is_active ? 'Ativo' : 'Inativo',
            'variable_names' => $this->getVariableNames(),
            'has_versions' => $this->parent_template_id !== null,
            
            // Actions disponíveis para o usuário atual
            'available_actions' => $this->getAvailableActions($request->user())
        ];
    }
    
    /**
     * Obter descrição da categoria
     */
    private function getCategoryDisplay(): string
    {
        return match ($this->category) {
            'skin_sale' => 'Venda de Skins',
            'skin_trade' => 'Troca de Skins',
            'service' => 'Prestação de Serviços',
            'marketplace' => 'Marketplace',
            'partnership' => 'Parceria',
            'terms' => 'Termos e Condições',
            'privacy' => 'Política de Privacidade',
            default => 'Categoria Personalizada'
        };
    }
    
    /**
     * Obter nomes das variáveis
     */
    private function getVariableNames(): array
    {
        if (!$this->variables || !is_array($this->variables)) {
            return [];
        }
        
        return collect($this->variables)->pluck('name')->toArray();
    }
    
    /**
     * Obter ações disponíveis para o usuário
     */
    private function getAvailableActions($user): array
    {
        if (!$user) {
            return ['view'];
        }
        
        $actions = ['view', 'use'];
        
        // Criador pode editar seus próprios templates
        if ($this->created_by === $user->id) {
            $actions[] = 'edit';
            $actions[] = 'duplicate';
            $actions[] = 'create_version';
            
            if ($this->is_active) {
                $actions[] = 'deactivate';
            } else {
                $actions[] = 'activate';
            }
        }
        
        // Admins podem fazer tudo
        if ($user->hasRole('admin')) {
            $actions = array_merge($actions, [
                'edit',
                'duplicate',
                'create_version',
                'activate',
                'deactivate',
                'view_usage',
                'delete'
            ]);
        }
        
        return array_unique($actions);
    }
}
