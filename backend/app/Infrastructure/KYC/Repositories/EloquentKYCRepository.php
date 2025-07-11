<?php

namespace App\Infrastructure\KYC\Repositories;

use App\Domain\KYC\Entities\KYCRequest;
use App\Domain\KYC\Entities\KYCDocument;
use App\Domain\KYC\Entities\KYCReview;
use App\Domain\KYC\Repositories\KYCRepositoryInterface;
use App\Models\KYCRequest as KYCRequestModel;
use App\Models\KYCDocument as KYCDocumentModel;
use App\Models\KYCReview as KYCReviewModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentKYCRepository implements KYCRepositoryInterface
{
    public function createRequest(array $data): KYCRequest
    {
        $model = KYCRequestModel::create($data);
        return $this->toKYCRequestEntity($model);
    }

    public function getRequestById(string $id): ?KYCRequest
    {
        $model = KYCRequestModel::find($id);
        return $model ? $this->toKYCRequestEntity($model) : null;
    }

    public function updateRequest(string $id, array $data): bool
    {
        return KYCRequestModel::where('id', $id)->update($data);
    }

    public function getActiveKYCByUser(string $userId): ?KYCRequest
    {
        $model = KYCRequestModel::where('user_id', $userId)
                               ->whereIn('status', ['pending', 'under_review'])
                               ->first();
        return $model ? $this->toKYCRequestEntity($model) : null;
    }

    public function getLatestKYCByUser(string $userId): ?KYCRequest
    {
        $model = KYCRequestModel::where('user_id', $userId)
                               ->latest()
                               ->first();
        return $model ? $this->toKYCRequestEntity($model) : null;
    }

    public function createDocument(array $data): KYCDocument
    {
        $model = KYCDocumentModel::create($data);
        return $this->toKYCDocumentEntity($model);
    }

    public function getDocumentById(string $id): ?KYCDocument
    {
        $model = KYCDocumentModel::find($id);
        return $model ? $this->toKYCDocumentEntity($model) : null;
    }

    public function updateDocument(string $id, array $data): bool
    {
        return KYCDocumentModel::where('id', $id)->update($data);
    }

    public function getDocumentByType(string $kycRequestId, string $documentType): ?KYCDocument
    {
        $model = KYCDocumentModel::where('kyc_request_id', $kycRequestId)
                                 ->where('document_type', $documentType)
                                 ->where('status', '!=', 'superseded')
                                 ->first();
        return $model ? $this->toKYCDocumentEntity($model) : null;
    }

    public function getRequestDocuments(string $kycRequestId): Collection
    {
        $models = KYCDocumentModel::where('kyc_request_id', $kycRequestId)->get();
        return $models->map(fn($model) => $this->toKYCDocumentEntity($model));
    }

    public function createReview(array $data): KYCReview
    {
        $model = KYCReviewModel::create($data);
        return $this->toKYCReviewEntity($model);
    }

    public function getRequestReviews(string $kycRequestId): Collection
    {
        $models = KYCReviewModel::where('kyc_request_id', $kycRequestId)->get();
        return $models->map(fn($model) => $this->toKYCReviewEntity($model));
    }

    public function getPendingRequests(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $paginator = KYCRequestModel::where('status', 'pending')
                                   ->with(['user', 'documents'])
                                   ->paginate($perPage, ['*'], 'page', $page);
        
        $items = $paginator->getCollection()->map(fn($model) => $this->toKYCRequestEntity($model));
        
        return new LengthAwarePaginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => request()->url()]
        );
    }

    public function getRequestsByStatus(string $status): Collection
    {
        $models = KYCRequestModel::where('status', $status)->get();
        return $models->map(fn($model) => $this->toKYCRequestEntity($model));
    }

    public function getStatistics(array $filters = []): array
    {
        $query = KYCRequestModel::query();

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

        $approved = $query->where('status', 'approved')->count();
        $pending = $query->where('status', 'pending')->count();
        $rejected = $query->where('status', 'rejected')->count();

        return [
            'total_requests' => $total,
            'by_status' => $byStatus,
            'approved_requests' => $approved,
            'pending_requests' => $pending,
            'rejected_requests' => $rejected,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0
        ];
    }

    /**
     * Converter modelo para entidade KYCRequest
     */
    private function toKYCRequestEntity(KYCRequestModel $model): KYCRequest
    {
        return new KYCRequest(
            id: $model->id,
            userId: $model->user_id,
            type: $model->type,
            status: $model->status,
            requiredDocuments: $model->required_documents ?? [],
            completedAt: $model->completed_at,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }

    /**
     * Converter modelo para entidade KYCDocument
     */
    private function toKYCDocumentEntity(KYCDocumentModel $model): KYCDocument
    {
        return new KYCDocument(
            id: $model->id,
            kycRequestId: $model->kyc_request_id,
            documentType: $model->document_type,
            filePath: $model->file_path,
            fileName: $model->file_name,
            fileSize: $model->file_size,
            mimeType: $model->mime_type,
            documentHash: $model->document_hash,
            status: $model->status,
            reviewedAt: $model->reviewed_at,
            reviewerId: $model->reviewer_id,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }

    /**
     * Converter modelo para entidade KYCReview
     */
    private function toKYCReviewEntity(KYCReviewModel $model): KYCReview
    {
        return new KYCReview(
            id: $model->id,
            kycRequestId: $model->kyc_request_id,
            documentId: $model->document_id,
            reviewerId: $model->reviewer_id,
            decision: $model->decision,
            comments: $model->comments,
            reviewedAt: $model->reviewed_at,
            metadata: $model->metadata ?? [],
            createdAt: $model->created_at,
            updatedAt: $model->updated_at
        );
    }
}
