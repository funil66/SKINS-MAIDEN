<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'transaction_id',
        'buyer_id',
        'seller_id',
        'template_id',
        'content',
        'status',
        'metadata',
        'signed_at',
        'expires_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'signed_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    /**
     * Relacionamento com template de contrato
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    /**
     * Relacionamento com assinaturas
     */
    public function signatures(): HasMany
    {
        return $this->hasMany(ContractSignature::class);
    }

    /**
     * Relacionamento com comprador
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Relacionamento com vendedor
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('buyer_id', $userId)
              ->orWhere('seller_id', $userId);
        });
    }

    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    public function scopePendingSignature($query)
    {
        return $query->where('status', 'pending_signature');
    }

    /**
     * Acessores
     */
    public function getIsSignedAttribute(): bool
    {
        return $this->status === 'signed';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getSignatureCountAttribute(): int
    {
        return $this->signatures()->whereNull('invalidated_at')->count();
    }
}
