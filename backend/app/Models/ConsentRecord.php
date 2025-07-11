<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentRecord extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'consent_records';

    protected $fillable = [
        'user_id',
        'purpose',
        'legal_basis',
        'data_types',
        'granted',
        'granted_at',
        'revoked_at',
        'revocation_reason',
        'expires_at',
        'ip_address',
        'user_agent',
        'metadata'
    ];

    protected $casts = [
        'data_types' => 'array',
        'granted' => 'boolean',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array'
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
    public function scopeGranted($query)
    {
        return $query->where('granted', true);
    }

    public function scopeRevoked($query)
    {
        return $query->whereNotNull('revoked_at');
    }

    public function scopeActive($query)
    {
        return $query->where('granted', true)
                    ->whereNull('revoked_at')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeByPurpose($query, string $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Acessores
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->granted && 
               !$this->revoked_at && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getPurposeDisplayAttribute(): string
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

    public function getLegalBasisDisplayAttribute(): string
    {
        return match ($this->legal_basis) {
            'consent' => 'Consentimento',
            'contract' => 'Execução de Contrato',
            'legal_obligation' => 'Obrigação Legal',
            'vital_interests' => 'Interesses Vitais',
            'public_task' => 'Tarefa Pública',
            'legitimate_interests' => 'Interesses Legítimos',
            default => 'Base Legal Desconhecida'
        };
    }
}
