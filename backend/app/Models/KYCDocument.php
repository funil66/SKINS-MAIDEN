<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KYCDocument extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kyc_documents';

    protected $fillable = [
        'kyc_request_id',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'document_hash',
        'status',
        'reviewed_at',
        'reviewer_id',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'reviewed_at' => 'datetime'
    ];

    protected $hidden = [
        'file_path' // Proteger caminho do arquivo
    ];

    /**
     * Relacionamento com solicitação KYC
     */
    public function kycRequest(): BelongsTo
    {
        return $this->belongsTo(KYCRequest::class);
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
    public function scopeByType($query, string $documentType)
    {
        return $query->where('document_type', $documentType);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Acessores
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
