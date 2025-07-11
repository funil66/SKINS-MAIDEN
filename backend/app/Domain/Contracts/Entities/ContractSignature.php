<?php

namespace App\Domain\Contracts\Entities;

use App\Domain\Shared\Entities\BaseEntity;
use Illuminate\Support\Str;

class ContractSignature extends BaseEntity
{
    protected $fillable = [
        'contract_id',
        'user_id',
        'signature_hash',
        'signature_type',
        'signature_data',
        'ip_address',
        'user_agent',
        'verification_data',
        'signed_at'
    ];

    protected $casts = [
        'verification_data' => 'array',
        'signed_at' => 'datetime'
    ];

    public const SIGNATURE_TYPES = [
        'digital' => 'Assinatura Digital',
        'electronic' => 'Assinatura Eletrônica',
        'wet' => 'Assinatura Física Digitalizada'
    ];

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
            
            if (empty($model->signed_at)) {
                $model->signed_at = now();
            }
            
            if (empty($model->signature_hash)) {
                $model->signature_hash = hash('sha256', 
                    $model->contract_id . 
                    $model->user_id . 
                    $model->signed_at->timestamp . 
                    $model->ip_address
                );
            }
        });
    }

    /**
     * Criar assinatura digital
     */
    public static function createDigitalSignature(
        string $contractId,
        string $userId,
        string $ipAddress,
        string $userAgent,
        ?string $signatureData = null,
        array $verificationData = []
    ): self {
        return self::create([
            'contract_id' => $contractId,
            'user_id' => $userId,
            'signature_type' => 'digital',
            'signature_data' => $signatureData,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'verification_data' => $verificationData,
            'signed_at' => now()
        ]);
    }

    /**
     * Verificar integridade da assinatura
     */
    public function verifyIntegrity(): bool
    {
        $expectedHash = hash('sha256', 
            $this->contract_id . 
            $this->user_id . 
            $this->signed_at->timestamp . 
            $this->ip_address
        );
        
        return hash_equals($this->signature_hash, $expectedHash);
    }

    /**
     * Obter informações de verificação formatadas
     */
    public function getVerificationInfo(): array
    {
        return [
            'signature_valid' => $this->verifyIntegrity(),
            'signed_at' => $this->signed_at->format('d/m/Y H:i:s'),
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'signature_type' => self::SIGNATURE_TYPES[$this->signature_type] ?? $this->signature_type,
            'verification_data' => $this->verification_data ?? []
        ];
    }

    /**
     * Scope por contrato
     */
    public function scopeForContract($query, string $contractId)
    {
        return $query->where('contract_id', $contractId);
    }

    /**
     * Scope por usuário
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope por tipo de assinatura
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('signature_type', $type);
    }

    /**
     * Relação com contrato
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    /**
     * Relação com usuário
     */
    public function user()
    {
        return $this->belongsTo(\App\Domain\Users\Entities\User::class, 'user_id');
    }
}
