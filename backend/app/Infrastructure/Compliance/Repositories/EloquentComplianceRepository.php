<?php

namespace App\Infrastructure\Compliance\Repositories;

use App\Domain\Compliance\Entities\ConsentRecord;
use App\Domain\Compliance\Entities\DataProcessingLog;
use App\Domain\Compliance\Entities\PrivacyRequest;
use App\Domain\Compliance\Repositories\ComplianceRepositoryInterface;
use App\Models\ConsentRecord as ConsentRecordModel;
use App\Models\DataProcessingLog as DataProcessingLogModel;
use App\Models\PrivacyRequest as PrivacyRequestModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentComplianceRepository implements ComplianceRepositoryInterface
{
    public function createConsent(array $data): ConsentRecord
    {
        $model = ConsentRecordModel::create($data);
        return $this->toConsentEntity($model);
    }

    public function getConsentById(string $id): ?ConsentRecord
    {
        $model = ConsentRecordModel::find($id);
        return $model ? $this->toConsentEntity($model) : null;
    }

    public function updateConsent(string $id, array $data): bool
    {
        return ConsentRecordModel::where('id', $id)->update($data);
    }

    public function getActiveConsent(string $userId, string $purpose): ?ConsentRecord
    {
        $model = ConsentRecordModel::where('user_id', $userId)
                                  ->where('purpose', $purpose)
                                  ->active()
                                  ->first();
        return $model ? $this->toConsentEntity($model) : null;
    }

    public function getUserConsents(string $userId): Collection
    {
        $models = ConsentRecordModel::where('user_id', $userId)->get();
        return $models->map(fn($model) => $this->toConsentEntity($model));
    }

    public function createPrivacyRequest(array $data): PrivacyRequest
    {
        $model = PrivacyRequestModel::create($data);
        return $this->toPrivacyRequestEntity($model);
    }

    public function getPrivacyRequestById(string $id): ?PrivacyRequest
    {
        $model = PrivacyRequestModel::find($id);
        return $model ? $this->toPrivacyRequestEntity($model) : null;
    }

    public function updatePrivacyRequest(string $id, array $data): PrivacyRequest
    {
        PrivacyRequestModel::where('id', $id)->update($data);
        $model = PrivacyRequestModel::find($id);
        return $this->toPrivacyRequestEntity($model);
    }

    public function getPendingPrivacyRequest(string $userId, string $requestType): ?PrivacyRequest
    {
        $model = PrivacyRequestModel::where('user_id', $userId)
                                   ->where('request_type', $requestType)
                                   ->where('status', 'pending')
                                   ->first();
        return $model ? $this->toPrivacyRequestEntity($model) : null;
    }

    public function getUserPrivacyRequests(string $userId): Collection
    {
        $models = PrivacyRequestModel::where('user_id', $userId)->get();
        return $models->map(fn($model) => $this->toPrivacyRequestEntity($model));
    }

    public function createProcessingLog(array $data): DataProcessingLog
    {
        $model = DataProcessingLogModel::create($data);
        return $this->toProcessingLogEntity($model);
    }

    public function getUserProcessingLogs(string $userId, int $days = 30): Collection
    {
        $models = DataProcessingLogModel::where('user_id', $userId)
                                       ->where('timestamp', '>=', now()->subDays($days))
                                       ->get();
        return $models->map(fn($model) => $this->toProcessingLogEntity($model));
    }

    public function getPendingPrivacyRequests(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $paginator = PrivacyRequestModel::where('status', 'pending')
                                       ->with(['user'])
                                       ->paginate($perPage, ['*'], 'page', $page);
        
        $items = $paginator->getCollection()->map(fn($model) => $this->toPrivacyRequestEntity($model));
        
        return new LengthAwarePaginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => request()->url()]
        );
    }

    public function getComplianceStatistics(array $filters = []): array
    {
        $consentQuery = ConsentRecordModel::query();
        $requestQuery = PrivacyRequestModel::query();

        if (isset($filters['date_from'])) {
            $consentQuery->where('created_at', '>=', $filters['date_from']);
            $requestQuery->where('requested_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $consentQuery->where('created_at', '<=', $filters['date_to']);
            $requestQuery->where('requested_at', '<=', $filters['date_to']);
        }

        $totalConsents = $consentQuery->count();
        $activeConsents = $consentQuery->active()->count();
        $revokedConsents = $consentQuery->revoked()->count();

        $totalRequests = $requestQuery->count();
        $pendingRequests = $requestQuery->where('status', 'pending')->count();
        $completedRequests = $requestQuery->where('status', 'completed')->count();

        return [
            'consents' => [
                'total' => $totalConsents,
                'active' => $activeConsents,
                'revoked' => $revokedConsents,
                'revocation_rate' => $totalConsents > 0 ? round(($revokedConsents / $totalConsents) * 100, 2) : 0
            ],
            'privacy_requests' => [
                'total' => $totalRequests,
                'pending' => $pendingRequests,
                'completed' => $completedRequests,
                'completion_rate' => $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 2) : 0
            ]
        ];
    }

    /**
     * Converter modelo para entidade ConsentRecord
     */
    private function toConsentEntity(ConsentRecordModel $model): ConsentRecord
    {
        return new ConsentRecord(
            id: $model->id,
            userId: $model->user_id,
            purpose: $model->purpose,
            legalBasis: $model->legal_basis,
            dataTypes: $model->data_types ?? [],
            granted: $model->granted,
            grantedAt: $model->granted_at,
            revokedAt: $model->revoked_at,
            revocationReason: $model->revocation_reason,
            expiresAt: $model->expires_at,
            ipAddress: $model->ip_address,
            userAgent: $model->user_agent,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }

    /**
     * Converter modelo para entidade PrivacyRequest
     */
    private function toPrivacyRequestEntity(PrivacyRequestModel $model): PrivacyRequest
    {
        return new PrivacyRequest(
            id: $model->id,
            userId: $model->user_id,
            requestType: $model->request_type,
            description: $model->description,
            status: $model->status,
            requestedAt: $model->requested_at,
            deadline: $model->deadline,
            response: $model->response,
            respondedBy: $model->responded_by,
            respondedAt: $model->responded_at,
            attachments: $model->attachments ?? [],
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }

    /**
     * Converter modelo para entidade DataProcessingLog
     */
    private function toProcessingLogEntity(DataProcessingLogModel $model): DataProcessingLog
    {
        return new DataProcessingLog(
            id: $model->id,
            userId: $model->user_id,
            actionType: $model->action_type,
            legalBasis: $model->legal_basis,
            details: $model->details ?? [],
            ipAddress: $model->ip_address,
            userAgent: $model->user_agent,
            timestamp: $model->timestamp,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }
}
