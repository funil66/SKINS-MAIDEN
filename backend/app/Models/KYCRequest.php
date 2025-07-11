<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KYCRequest extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kyc_requests';

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'required_documents',
        'completed_at',
        'metadata'
    ];

    protected $casts = [
        'required_documents' => 'array',
        'metadata' => 'array',
        'completed_at' => 'datetime'
    ];

    /**
     * Relacionamento com usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com documentos
     */
    public function documents(): HasMany
    {
        return $this->hasMany(KYCDocument::class);
    }

    /**
     * Relacionamento com revisões
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(KYCReview::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Acessores
     */
    public function getIsCompletedAttribute(): bool
    {
        return in_array($this->status, ['approved', 'rejected']);
    }

    public function getDocumentCountAttribute(): int
    {
        return $this->documents()->count();
    }

    public function getApprovedDocumentsCountAttribute(): int
    {
        return $this->documents()->where('status', 'approved')->count();
    }
}
