<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacyRequest extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'privacy_requests';

    protected $fillable = [
        'user_id',
        'request_type',
        'description',
        'status',
        'requested_at',
        'deadline',
        'response',
        'responded_by',
        'responded_at',
        'attachments',
        'metadata'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'deadline' => 'datetime',
        'responded_at' => 'datetime',
        'attachments' => 'array',
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
     * Relacionamento com quem respondeu
     */
    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByType($query, string $requestType)
    {
        return $query->where('request_type', $requestType);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                    ->where('deadline', '<', now());
    }

    /**
     * Acessores
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->deadline->isPast();
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->status !== 'pending') {
            return 0;
        }

        return max(0, now()->diffInDays($this->deadline, false));
    }

    public function getRequestTypeDisplayAttribute(): string
    {
        return match ($this->request_type) {
            'access' => 'Acesso aos Dados',
            'rectification' => 'Retificação de Dados',
            'erasure' => 'Apagamento de Dados',
            'portability' => 'Portabilidade de Dados',
            'restriction' => 'Restrição de Processamento',
            'objection' => 'Oposição ao Processamento',
            default => 'Tipo Desconhecido'
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'in_progress' => 'Em Andamento',
            'completed' => 'Concluída',
            'rejected' => 'Rejeitada',
            default => 'Status Desconhecido'
        };
    }
}
