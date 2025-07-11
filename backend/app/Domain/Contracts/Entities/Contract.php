<?php

namespace App\Domain\Contracts\Entities;

use App\Domain\Shared\Entities\BaseEntity;
use Illuminate\Support\Str;

class Contract extends BaseEntity
{
    protected $fillable = [
        'transaction_id',
        'buyer_id',
        'seller_id',
        'template_id',
        'content',
        'status',
        'hash_sha256',
        'metadata',
        'signed_at',
        'completed_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'signed_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public const STATUSES = [
        'draft' => 'Rascunho',
        'pending_signature' => 'Aguardando Assinatura',
        'partially_signed' => 'Parcialmente Assinado',
        'signed' => 'Assinado',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado'
    ];

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
            
            if (empty($model->hash_sha256)) {
                $model->hash_sha256 = hash('sha256', $model->content . $model->transaction_id . time());
            }
        });
    }

    /**
     * Verificar se o contrato está assinado por todas as partes
     */
    public function isFullySigned(): bool
    {
        $requiredSignatures = [$this->buyer_id, $this->seller_id];
        $signatures = $this->signatures()->pluck('user_id')->toArray();
        
        return empty(array_diff($requiredSignatures, $signatures));
    }

    /**
     * Verificar se o usuário já assinou
     */
    public function isSignedBy(string $userId): bool
    {
        return $this->signatures()->where('user_id', $userId)->exists();
    }

    /**
     * Obter assinaturas pendentes
     */
    public function getPendingSignatures(): array
    {
        $requiredSignatures = [$this->buyer_id, $this->seller_id];
        $signatures = $this->signatures()->pluck('user_id')->toArray();
        
        return array_diff($requiredSignatures, $signatures);
    }

    /**
     * Marcar como assinado se todas as partes assinaram
     */
    public function updateSignatureStatus(): void
    {
        if ($this->isFullySigned() && $this->status !== 'signed') {
            $this->update([
                'status' => 'signed',
                'signed_at' => now()
            ]);
        } elseif (!$this->isFullySigned() && $this->signatures()->exists()) {
            $this->update(['status' => 'partially_signed']);
        }
    }

    /**
     * Verificar integridade do contrato
     */
    public function verifyIntegrity(): bool
    {
        $expectedHash = hash('sha256', $this->content . $this->transaction_id . $this->created_at->timestamp);
        return hash_equals($this->hash_sha256, $expectedHash);
    }

    /**
     * Scope para contratos ativos
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled']);
    }

    /**
     * Scope por status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope por usuário (comprador ou vendedor)
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('buyer_id', $userId)
              ->orWhere('seller_id', $userId);
        });
    }

    /**
     * Relação com template
     */
    public function template()
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    /**
     * Relação com comprador
     */
    public function buyer()
    {
        return $this->belongsTo(\App\Domain\Users\Entities\User::class, 'buyer_id');
    }

    /**
     * Relação com vendedor
     */
    public function seller()
    {
        return $this->belongsTo(\App\Domain\Users\Entities\User::class, 'seller_id');
    }

    /**
     * Relação com assinaturas
     */
    public function signatures()
    {
        return $this->hasMany(ContractSignature::class, 'contract_id');
    }

    /**
     * Relação com transação
     */
    public function transaction()
    {
        return $this->belongsTo(\App\Domain\Transactions\Entities\Transaction::class, 'transaction_id');
    }
}
