<?php

namespace App\Domain\Contracts\Entities;

use App\Domain\Shared\Entities\BaseEntity;
use Illuminate\Support\Str;

class ContractTemplate extends BaseEntity
{
    protected $fillable = [
        'name',
        'category',
        'content',
        'variables',
        'version',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean'
    ];

    public const CATEGORIES = [
        'escrow' => 'Contrato de Escrow',
        'direct_sale' => 'Venda Direta',
        'auction' => 'Leilão'
    ];

    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }

    /**
     * Gerar contrato a partir do template
     */
    public function generateContract(array $variables): string
    {
        $content = $this->content;
        
        foreach ($variables as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }
        
        return $content;
    }

    /**
     * Validar se todas as variáveis necessárias estão presentes
     */
    public function validateVariables(array $variables): array
    {
        $missing = [];
        $templateVariables = $this->variables ?? [];
        
        foreach ($templateVariables as $variable) {
            if ($variable['required'] && !isset($variables[$variable['name']])) {
                $missing[] = $variable['name'];
            }
        }
        
        return $missing;
    }

    /**
     * Obter próxima versão do template
     */
    public function getNextVersion(): string
    {
        $current = $this->version;
        $parts = explode('.', $current);
        $major = (int) $parts[0];
        $minor = isset($parts[1]) ? (int) $parts[1] : 0;
        
        return $major . '.' . ($minor + 1);
    }

    /**
     * Scope para templates ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope por categoria
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Relação com contratos gerados
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'template_id');
    }

    /**
     * Relação com o criador
     */
    public function creator()
    {
        return $this->belongsTo(\App\Domain\Users\Entities\User::class, 'created_by');
    }
}
