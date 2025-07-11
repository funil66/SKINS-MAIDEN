<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'category',
        'content',
        'variables',
        'version',
        'parent_template_id',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Relacionamento com template pai
     */
    public function parentTemplate(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'parent_template_id');
    }

    /**
     * Relacionamento com versões filhas
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ContractTemplate::class, 'parent_template_id');
    }

    /**
     * Relacionamento com contratos gerados
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'template_id');
    }

    /**
     * Relacionamento com criador
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeLatestVersion($query)
    {
        return $query->whereNull('parent_template_id')
                    ->orWhereRaw('id = parent_template_id');
    }

    /**
     * Acessores
     */
    public function getUsageCountAttribute(): int
    {
        return $this->contracts()->count();
    }

    public function getLastUsedAtAttribute()
    {
        return $this->contracts()->max('created_at');
    }

    public function getVariableNamesAttribute(): array
    {
        return collect($this->variables)->pluck('name')->toArray();
    }
}
