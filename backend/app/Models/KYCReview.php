<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KYCReview extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kyc_reviews';

    protected $fillable = [
        'kyc_request_id',
        'document_id',
        'reviewer_id',
        'decision',
        'comments',
        'reviewed_at',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'reviewed_at' => 'datetime'
    ];

    /**
     * Relacionamento com solicitação KYC
     */
    public function kycRequest(): BelongsTo
    {
        return $this->belongsTo(KYCRequest::class);
    }

    /**
     * Relacionamento com documento
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(KYCDocument::class);
    }

    /**
     * Relacionamento com revisor
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Scopes
     */
    public function scopeByDecision($query, string $decision)
    {
        return $query->where('decision', $decision);
    }

    public function scopeApproved($query)
    {
        return $query->where('decision', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('decision', 'rejected');
    }

    public function scopeByReviewer($query, string $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    /**
     * Acessores
     */
    public function getDecisionDisplayAttribute(): string
    {
        return match ($this->decision) {
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            'needs_clarification' => 'Necessita Esclarecimento',
            default => 'Decisão Desconhecida'
        };
    }
}
