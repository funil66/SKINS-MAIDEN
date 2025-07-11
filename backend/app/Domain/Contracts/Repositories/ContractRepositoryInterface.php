<?php

namespace App\Domain\Contracts\Repositories;

use App\Domain\Contracts\Entities\Contract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ContractRepositoryInterface
{
    /**
     * Criar novo contrato
     */
    public function create(array $data): Contract;

    /**
     * Buscar contrato por ID
     */
    public function findById(string $id): ?Contract;

    /**
     * Buscar contrato por hash
     */
    public function findByHash(string $hash): ?Contract;

    /**
     * Buscar contratos por transação
     */
    public function findByTransactionId(string $transactionId): Collection;

    /**
     * Buscar contratos de um usuário
     */
    public function findByUserId(string $userId, array $filters = []): Collection;

    /**
     * Listar contratos com paginação
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar contratos por status
     */
    public function findByStatus(string $status): Collection;

    /**
     * Buscar contratos pendentes de assinatura para um usuário
     */
    public function findPendingSignatureForUser(string $userId): Collection;

    /**
     * Atualizar contrato
     */
    public function update(string $id, array $data): bool;

    /**
     * Deletar contrato
     */
    public function delete(string $id): bool;

    /**
     * Buscar contratos expirados
     */
    public function findExpired(): Collection;

    /**
     * Estatísticas de contratos
     */
    public function getStatistics(array $filters = []): array;

    /**
     * Buscar contratos por período
     */
    public function findByDateRange(\DateTime $start, \DateTime $end): Collection;
}
