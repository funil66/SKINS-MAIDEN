<?php

namespace App\Domain\Contracts\Repositories;

use App\Domain\Contracts\Entities\ContractTemplate;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ContractTemplateRepositoryInterface
{
    /**
     * Criar novo template
     */
    public function create(array $data): ContractTemplate;

    /**
     * Buscar template por ID
     */
    public function findById(string $id): ?ContractTemplate;

    /**
     * Listar templates ativos
     */
    public function findActive(): Collection;

    /**
     * Buscar templates por categoria
     */
    public function findByCategory(string $category): Collection;

    /**
     * Listar templates com paginação
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Atualizar template
     */
    public function update(string $id, array $data): bool;

    /**
     * Desativar template
     */
    public function deactivate(string $id): bool;

    /**
     * Buscar template mais recente por categoria
     */
    public function findLatestByCategory(string $category): ?ContractTemplate;

    /**
     * Criar nova versão de template
     */
    public function createNewVersion(string $templateId, array $data): ContractTemplate;

    /**
     * Buscar histórico de versões
     */
    public function findVersionHistory(string $templateId): Collection;

    /**
     * Estatísticas de uso dos templates
     */
    public function getUsageStatistics(): array;
}
