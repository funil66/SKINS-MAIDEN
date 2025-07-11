<?php

namespace App\Domain\KYC\Repositories;

use App\Domain\KYC\Entities\KYCRequest;
use App\Domain\KYC\Entities\KYCDocument;
use App\Domain\KYC\Entities\KYCReview;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface KYCRepositoryInterface
{
    /**
     * Criar nova solicitação KYC
     */
    public function createRequest(array $data): KYCRequest;

    /**
     * Buscar solicitação por ID
     */
    public function getRequestById(string $id): ?KYCRequest;

    /**
     * Atualizar solicitação KYC
     */
    public function updateRequest(string $id, array $data): bool;

    /**
     * Buscar KYC ativo por usuário
     */
    public function getActiveKYCByUser(string $userId): ?KYCRequest;

    /**
     * Buscar último KYC por usuário
     */
    public function getLatestKYCByUser(string $userId): ?KYCRequest;

    /**
     * Criar documento KYC
     */
    public function createDocument(array $data): KYCDocument;

    /**
     * Buscar documento por ID
     */
    public function getDocumentById(string $id): ?KYCDocument;

    /**
     * Atualizar documento
     */
    public function updateDocument(string $id, array $data): bool;

    /**
     * Buscar documento por tipo
     */
    public function getDocumentByType(string $kycRequestId, string $documentType): ?KYCDocument;

    /**
     * Buscar documentos da solicitação
     */
    public function getRequestDocuments(string $kycRequestId): Collection;

    /**
     * Criar revisão
     */
    public function createReview(array $data): KYCReview;

    /**
     * Buscar revisões da solicitação
     */
    public function getRequestReviews(string $kycRequestId): Collection;

    /**
     * Buscar solicitações pendentes
     */
    public function getPendingRequests(int $page = 1, int $perPage = 15): LengthAwarePaginator;

    /**
     * Buscar solicitações por status
     */
    public function getRequestsByStatus(string $status): Collection;

    /**
     * Obter estatísticas de KYC
     */
    public function getStatistics(array $filters = []): array;
}
