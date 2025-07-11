<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractSignature extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'contract_id',
        'signer_id',
        'signer_type',
        'signature_hash',
        'signature_data',
        'content_hash',
        'signed_at',
        'ip_address',
        'user_agent',
        'metadata',
        'invalidated_at',
        'invalidated_by',
        'invalidation_reason'
    ];

    protected $casts = [
        'metadata' => 'array',
        'signed_at' => 'datetime',
        'invalidated_at' => 'datetime'
    ];

    protected $hidden = [
        'signature_data' // Proteger dados da assinatura
    ];

    /**
     * Relacionamento com contrato
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Relacionamento com assinante
     */
    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_id');
    }

    /**
     * Relacionamento com quem invalidou
     */
    public function invalidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invalidated_by');
    }

    /**
     * Scopes
     */
    public function scopeValid($query)
    {
        return $query->whereNull('invalidated_at');
    }

    public function scopeInvalidated($query)
    {
        return $query->whereNotNull('invalidated_at');
    }

    public function scopeBySigner($query, string $signerId)
    {
        return $query->where('signer_id', $signerId);
    }

    public function scopeByType($query, string $signerType)
    {
        return $query->where('signer_type', $signerType);
    }

    /**
     * Acessores
     */
    public function getIsValidAttribute(): bool
    {
        return $this->invalidated_at === null;
    }

    public function getSignerNameAttribute(): string
    {
        return $this->signer ? $this->signer->name : 'Usuário não encontrado';
    }
}
