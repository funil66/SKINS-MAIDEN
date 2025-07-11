<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataProcessingLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'data_processing_logs';

    protected $fillable = [
        'user_id',
        'action_type',
        'legal_basis',
        'details',
        'ip_address',
        'user_agent',
        'timestamp'
    ];

    protected $casts = [
        'details' => 'array',
        'timestamp' => 'datetime'
    ];

    /**
     * Relacionamento com usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByActionType($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('timestamp', '>=', now()->subDays($days));
    }

    /**
     * Acessores
     */
    public function getActionTypeDisplayAttribute(): string
    {
        return match ($this->action_type) {
            'data_collection' => 'Coleta de Dados',
            'data_processing' => 'Processamento de Dados',
            'data_sharing' => 'Compartilhamento de Dados',
            'data_deletion' => 'Exclusão de Dados',
            'consent_recorded' => 'Consentimento Registrado',
            'consent_revoked' => 'Consentimento Revogado',
            'privacy_request_created' => 'Solicitação de Privacidade Criada',
            'privacy_request_responded' => 'Solicitação de Privacidade Respondida',
            'data_export' => 'Exportação de Dados',
            'data_rectification' => 'Retificação de Dados',
            default => 'Ação Desconhecida'
        };
    }
}
