<?php

namespace App\Domain\Compliance\Repositories;

use App\Domain\Compliance\Entities\ConsentRecord;
use App\Domain\Compliance\Entities\DataProcessingLog;
use App\Domain\Compliance\Entities\PrivacyRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ComplianceRepositoryInterface
{
    /**
     * Criar registro de consentimento
     */
    public function createConsent(array $data): ConsentRecord;

    /**
     * Buscar consentimento por ID
     */
    public function getConsentById(string $id): ?ConsentRecord;

    /**
     * Atualizar consentimento
     */
    public function updateConsent(string $id, array $data): bool;

    /**
     * Buscar consentimento ativo por usuário e finalidade
     */
    public function getActiveConsent(string $userId, string $purpose): ?ConsentRecord;

    /**
     * Buscar consentimentos do usuário
     */
    public function getUserConsents(string $userId): Collection;

    /**
     * Criar solicitação de privacidade
     */
    public function createPrivacyRequest(array $data): PrivacyRequest;

    /**
     * Buscar solicitação de privacidade por ID
     */
    public function getPrivacyRequestById(string $id): ?PrivacyRequest;

    /**
     * Atualizar solicitação de privacidade
     */
    public function updatePrivacyRequest(string $id, array $data): PrivacyRequest;

    /**
     * Buscar solicitação pendente por tipo
     */
    public function getPendingPrivacyRequest(string $userId, string $requestType): ?PrivacyRequest;

    /**
     * Buscar solicitações do usuário
     */
    public function getUserPrivacyRequests(string $userId): Collection;

    /**
     * Criar log de processamento
     */
    public function createProcessingLog(array $data): DataProcessingLog;

    /**
     * Buscar logs do usuário
     */
    public function getUserProcessingLogs(string $userId, int $days = 30): Collection;

    /**
     * Buscar solicitações pendentes
     */
    public function getPendingPrivacyRequests(int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Obter estatísticas de compliance
     */
    public function getComplianceStatistics(array $filters = []): array;
}
