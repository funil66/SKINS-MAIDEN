<?php

namespace App\Infrastructure\Contracts\Repositories;

use App\Domain\Contracts\Entities\ContractTemplate;
use App\Domain\Contracts\Repositories\ContractTemplateRepositoryInterface;
use App\Models\ContractTemplate as ContractTemplateModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentContractTemplateRepository implements ContractTemplateRepositoryInterface
{
    public function create(array $data): ContractTemplate
    {
        $model = ContractTemplateModel::create($data);
        return $this->toEntity($model);
    }

    public function findById(string $id): ?ContractTemplate
    {
        $model = ContractTemplateModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function update(string $id, array $data): bool
    {
        return ContractTemplateModel::where('id', $id)->update($data);
    }

    public function delete(string $id): bool
    {
        return ContractTemplateModel::where('id', $id)->delete();
    }

    public function findByCategory(string $category): Collection
    {
        $models = ContractTemplateModel::where('category', $category)
                                      ->where('is_active', true)
                                      ->get();
        
        return $models->map(fn($model) => $this->toEntity($model));
    }

    public function findActive(): Collection
    {
        $models = ContractTemplateModel::where('is_active', true)->get();
        return $models->map(fn($model) => $this->toEntity($model));
    }

    public function search(array $filters, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = ContractTemplateModel::query();

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        
        // Converter modelos para entidades
        $items = $paginator->getCollection()->map(fn($model) => $this->toEntity($model));
        
        return new LengthAwarePaginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => request()->url()]
        );
    }

    public function createVersion(string $templateId, array $changes): ContractTemplate
    {
        $original = ContractTemplateModel::findOrFail($templateId);
        
        // Incrementar versão
        $newVersion = $this->getNextVersion($original->version);
        
        // Criar nova versão
        $data = array_merge($original->toArray(), $changes, [
            'id' => \Illuminate\Support\Str::uuid(),
            'version' => $newVersion,
            'parent_template_id' => $original->parent_template_id ?? $templateId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        unset($data['created_at'], $data['updated_at']); // Deixar o Eloquent gerenciar
        
        $model = ContractTemplateModel::create($data);
        return $this->toEntity($model);
    }

    public function getVersions(string $templateId): Collection
    {
        $template = ContractTemplateModel::findOrFail($templateId);
        $parentId = $template->parent_template_id ?? $templateId;
        
        $models = ContractTemplateModel::where(function ($query) use ($parentId, $templateId) {
            $query->where('parent_template_id', $parentId)
                  ->orWhere('id', $parentId);
        })->orderBy('created_at')->get();
        
        return $models->map(fn($model) => $this->toEntity($model));
    }

    public function getUsageStatistics(string $templateId): array
    {
        $template = ContractTemplateModel::findOrFail($templateId);
        
        // Contar contratos gerados
        $totalContracts = \App\Models\Contract::where('template_id', $templateId)->count();
        $signedContracts = \App\Models\Contract::where('template_id', $templateId)
                                              ->where('status', 'signed')
                                              ->count();
        
        // Estatísticas por período
        $last30Days = \App\Models\Contract::where('template_id', $templateId)
                                         ->where('created_at', '>=', now()->subDays(30))
                                         ->count();
        
        $last7Days = \App\Models\Contract::where('template_id', $templateId)
                                        ->where('created_at', '>=', now()->subDays(7))
                                        ->count();
        
        return [
            'template_id' => $templateId,
            'template_name' => $template->name,
            'total_contracts' => $totalContracts,
            'signed_contracts' => $signedContracts,
            'completion_rate' => $totalContracts > 0 ? round(($signedContracts / $totalContracts) * 100, 2) : 0,
            'usage_last_30_days' => $last30Days,
            'usage_last_7_days' => $last7Days,
            'created_at' => $template->created_at,
            'last_used_at' => \App\Models\Contract::where('template_id', $templateId)
                                                 ->max('created_at')
        ];
    }

    /**
     * Converter modelo Eloquent para entidade de domínio
     */
    private function toEntity(ContractTemplateModel $model): ContractTemplate
    {
        return new ContractTemplate(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            category: $model->category,
            content: $model->content,
            variables: $model->variables ?? [],
            version: $model->version,
            parentTemplateId: $model->parent_template_id,
            isActive: $model->is_active,
            createdBy: $model->created_by,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }

    /**
     * Calcular próxima versão
     */
    private function getNextVersion(string $currentVersion): string
    {
        $parts = explode('.', $currentVersion);
        $major = (int) ($parts[0] ?? 1);
        $minor = (int) ($parts[1] ?? 0);
        
        return $major . '.' . ($minor + 1);
    }
}
