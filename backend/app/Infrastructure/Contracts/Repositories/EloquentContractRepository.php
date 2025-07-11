<?php

namespace App\Infrastructure\Contracts\Repositories;

use App\Domain\Contracts\Entities\Contract;
use App\Domain\Contracts\Entities\ContractSignature;
use App\Domain\Contracts\Repositories\ContractRepositoryInterface;
use App\Models\Contract as ContractModel;
use App\Models\ContractSignature as ContractSignatureModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentContractRepository implements ContractRepositoryInterface
{
    public function create(array $data): Contract
    {
        $model = ContractModel::create($data);
        return $this->toEntity($model);
    }

    public function findById(string $id): ?Contract
    {
        $model = ContractModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function update(string $id, array $data): bool
    {
        return ContractModel::where('id', $id)->update($data);
    }

    public function delete(string $id): bool
    {
        return ContractModel::where('id', $id)->delete();
    }

    public function findByTransaction(string $transactionId): Collection
    {
        $models = ContractModel::where('transaction_id', $transactionId)->get();
        return $models->map(fn($model) => $this->toEntity($model));
    }

    public function findByUser(string $userId, string $role = null): Collection
    {
        $query = ContractModel::query();
        
        if ($role === 'buyer') {
            $query->where('buyer_id', $userId);
        } elseif ($role === 'seller') {
            $query->where('seller_id', $userId);
        } else {
            $query->where(function ($q) use ($userId) {
                $q->where('buyer_id', $userId)
                  ->orWhere('seller_id', $userId);
            });
        }
        
        $models = $query->get();
        return $models->map(fn($model) => $this->toEntity($model));
    }

    public function findByStatus(string $status): Collection
    {
        $models = ContractModel::where('status', $status)->get();
        return $models->map(fn($model) => $this->toEntity($model));
    }

    public function findByTemplate(string $templateId): Collection
    {
        $models = ContractModel::where('template_id', $templateId)->get();
        return $models->map(fn($model) => $this->toEntity($model));
    }

    public function search(array $filters, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = ContractModel::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('buyer_id', $filters['user_id'])
                  ->orWhere('seller_id', $filters['user_id']);
            });
        }

        if (isset($filters['template_id'])) {
            $query->where('template_id', $filters['template_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['search'])) {
            $query->where('content', 'like', '%' . $filters['search'] . '%');
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

    public function getContractSignatures(string $contractId): Collection
    {
        $models = ContractSignatureModel::where('contract_id', $contractId)->get();
        return $models->map(fn($model) => $this->toSignatureEntity($model));
    }

    public function findSignatureById(string $signatureId): ?ContractSignature
    {
        $model = ContractSignatureModel::find($signatureId);
        return $model ? $this->toSignatureEntity($model) : null;
    }

    public function createSignature(array $data): ContractSignature
    {
        $model = ContractSignatureModel::create($data);
        return $this->toSignatureEntity($model);
    }

    public function updateSignature(string $signatureId, array $data): bool
    {
        return ContractSignatureModel::where('id', $signatureId)->update($data);
    }

    public function getExpiringSoon(int $days = 30): Collection
    {
        $models = ContractModel::where('status', 'signed')
                               ->whereNotNull('expires_at')
                               ->where('expires_at', '<=', now()->addDays($days))
                               ->get();
        
        return $models->map(fn($model) => $this->toEntity($model));
    }

    public function getStatistics(array $filters = []): array
    {
        $query = ContractModel::query();

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $total = $query->count();
        $byStatus = $query->groupBy('status')
                         ->selectRaw('status, count(*) as count')
                         ->pluck('count', 'status')
                         ->toArray();

        $signed = $query->where('status', 'signed')->count();
        $pending = $query->where('status', 'pending_signature')->count();

        return [
            'total_contracts' => $total,
            'by_status' => $byStatus,
            'signed_contracts' => $signed,
            'pending_signature' => $pending,
            'completion_rate' => $total > 0 ? round(($signed / $total) * 100, 2) : 0
        ];
    }

    public function getRecentActivity(int $limit = 10): Collection
    {
        $models = ContractModel::orderBy('updated_at', 'desc')
                               ->limit($limit)
                               ->get();
        
        return $models->map(fn($model) => $this->toEntity($model));
    }

    /**
     * Converter modelo Eloquent para entidade de domínio
     */
    private function toEntity(ContractModel $model): Contract
    {
        return new Contract(
            id: $model->id,
            transactionId: $model->transaction_id,
            buyerId: $model->buyer_id,
            sellerId: $model->seller_id,
            templateId: $model->template_id,
            content: $model->content,
            status: $model->status,
            metadata: $model->metadata ?? [],
            signedAt: $model->signed_at,
            expiresAt: $model->expires_at,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }

    /**
     * Converter modelo de assinatura para entidade
     */
    private function toSignatureEntity(ContractSignatureModel $model): ContractSignature
    {
        return new ContractSignature(
            id: $model->id,
            contractId: $model->contract_id,
            signerId: $model->signer_id,
            signerType: $model->signer_type,
            signatureHash: $model->signature_hash,
            signatureData: $model->signature_data,
            contentHash: $model->content_hash,
            signedAt: $model->signed_at,
            ipAddress: $model->ip_address,
            userAgent: $model->user_agent,
            metadata: $model->metadata ?? [],
            invalidatedAt: $model->invalidated_at,
            invalidatedBy: $model->invalidated_by,
            invalidationReason: $model->invalidation_reason,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }
}
