<?php

namespace App\Domain\KYC\Services;

use App\Domain\KYC\Entities\KYCRequest;
use App\Domain\KYC\Entities\KYCDocument;
use App\Domain\KYC\Entities\KYCReview;
use App\Domain\KYC\Repositories\KYCRepositoryInterface;
use App\Domain\Shared\Exceptions\BusinessException;
use App\Domain\Shared\Services\FileService;
use App\Domain\Shared\Services\HashService;
use Illuminate\Support\Facades\Log;

class KYCVerificationService
{
    public function __construct(
        private KYCRepositoryInterface $kycRepository,
        private FileService $fileService,
        private HashService $hashService
    ) {}

    /**
     * Iniciar processo de verificação KYC
     */
    public function initiateKYC(
        string $userId,
        string $type = 'basic',
        array $metadata = []
    ): KYCRequest {
        // Verificar se já existe processo ativo
        $activeRequest = $this->kycRepository->getActiveKYCByUser($userId);
        
        if ($activeRequest) {
            throw new BusinessException("Usuário já possui processo KYC ativo");
        }

        // Criar nova solicitação
        $request = $this->kycRepository->createRequest([
            'user_id' => $userId,
            'type' => $type,
            'status' => 'pending',
            'required_documents' => $this->getRequiredDocuments($type),
            'metadata' => array_merge([
                'initiated_at' => now()->toISOString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ], $metadata)
        ]);

        Log::info('KYC process initiated', [
            'kyc_request_id' => $request->id,
            'user_id' => $userId,
            'type' => $type
        ]);

        return $request;
    }

    /**
     * Enviar documento para verificação
     */
    public function submitDocument(
        string $kycRequestId,
        string $documentType,
        string $filePath,
        array $metadata = []
    ): KYCDocument {
        $request = $this->kycRepository->getRequestById($kycRequestId);
        
        if (!$request) {
            throw new BusinessException("Solicitação KYC não encontrada: {$kycRequestId}");
        }

        if ($request->status !== 'pending') {
            throw new BusinessException("Solicitação KYC não está em status pendente");
        }

        // Verificar se documento é obrigatório
        if (!in_array($documentType, $request->required_documents)) {
            throw new BusinessException("Tipo de documento não é obrigatório: {$documentType}");
        }

        // Verificar se documento já foi enviado
        $existingDoc = $this->kycRepository->getDocumentByType($kycRequestId, $documentType);
        if ($existingDoc && $existingDoc->status !== 'rejected') {
            throw new BusinessException("Documento já foi enviado: {$documentType}");
        }

        // Processar arquivo
        $fileInfo = $this->fileService->processDocument($filePath);
        $documentHash = $this->hashService->hash($fileInfo['content']);

        // Criar documento
        $document = $this->kycRepository->createDocument([
            'kyc_request_id' => $kycRequestId,
            'document_type' => $documentType,
            'file_path' => $fileInfo['path'],
            'file_name' => $fileInfo['name'],
            'file_size' => $fileInfo['size'],
            'mime_type' => $fileInfo['mime_type'],
            'document_hash' => $documentHash,
            'status' => 'submitted',
            'metadata' => array_merge([
                'submitted_at' => now()->toISOString(),
                'file_hash' => $documentHash
            ], $metadata)
        ]);

        // Atualizar status da solicitação se todos documentos enviados
        $this->updateRequestStatus($request);

        Log::info('KYC document submitted', [
            'kyc_request_id' => $kycRequestId,
            'document_id' => $document->id,
            'document_type' => $documentType
        ]);

        return $document;
    }

    /**
     * Revisar documento KYC
     */
    public function reviewDocument(
        string $documentId,
        string $reviewerId,
        string $decision,
        string $comments = null,
        array $metadata = []
    ): KYCReview {
        $document = $this->kycRepository->getDocumentById($documentId);
        
        if (!$document) {
            throw new BusinessException("Documento não encontrado: {$documentId}");
        }

        if ($document->status !== 'submitted') {
            throw new BusinessException("Documento não está disponível para revisão");
        }

        if (!in_array($decision, ['approved', 'rejected', 'needs_clarification'])) {
            throw new BusinessException("Decisão inválida: {$decision}");
        }

        // Criar revisão
        $review = $this->kycRepository->createReview([
            'kyc_request_id' => $document->kyc_request_id,
            'document_id' => $documentId,
            'reviewer_id' => $reviewerId,
            'decision' => $decision,
            'comments' => $comments,
            'reviewed_at' => now(),
            'metadata' => array_merge([
                'review_timestamp' => now()->toISOString()
            ], $metadata)
        ]);

        // Atualizar status do documento
        $this->kycRepository->updateDocument($documentId, [
            'status' => $decision,
            'reviewed_at' => now(),
            'reviewer_id' => $reviewerId
        ]);

        // Atualizar status da solicitação
        $request = $this->kycRepository->getRequestById($document->kyc_request_id);
        $this->updateRequestStatus($request);

        Log::info('KYC document reviewed', [
            'document_id' => $documentId,
            'reviewer_id' => $reviewerId,
            'decision' => $decision,
            'kyc_request_id' => $document->kyc_request_id
        ]);

        return $review;
    }

    /**
     * Obter status do KYC do usuário
     */
    public function getUserKYCStatus(string $userId): array
    {
        $request = $this->kycRepository->getLatestKYCByUser($userId);
        
        if (!$request) {
            return [
                'status' => 'not_initiated',
                'verification_level' => 'none',
                'documents' => [],
                'next_actions' => ['initiate_kyc']
            ];
        }

        $documents = $this->kycRepository->getRequestDocuments($request->id);
        $reviews = $this->kycRepository->getRequestReviews($request->id);

        return [
            'status' => $request->status,
            'verification_level' => $this->getVerificationLevel($request),
            'initiated_at' => $request->created_at,
            'completed_at' => $request->completed_at,
            'documents' => $documents->map(function ($doc) {
                return [
                    'type' => $doc->document_type,
                    'status' => $doc->status,
                    'submitted_at' => $doc->created_at,
                    'reviewed_at' => $doc->reviewed_at
                ];
            })->toArray(),
            'reviews' => $reviews->map(function ($review) {
                return [
                    'document_type' => $review->document->document_type,
                    'decision' => $review->decision,
                    'comments' => $review->comments,
                    'reviewed_at' => $review->reviewed_at
                ];
            })->toArray(),
            'next_actions' => $this->getNextActions($request, $documents)
        ];
    }

    /**
     * Reenviar documento rejeitado
     */
    public function resubmitDocument(
        string $kycRequestId,
        string $documentType,
        string $filePath,
        array $metadata = []
    ): KYCDocument {
        $request = $this->kycRepository->getRequestById($kycRequestId);
        
        if (!$request) {
            throw new BusinessException("Solicitação KYC não encontrada");
        }

        // Verificar se existe documento rejeitado deste tipo
        $existingDoc = $this->kycRepository->getDocumentByType($kycRequestId, $documentType);
        if (!$existingDoc || $existingDoc->status !== 'rejected') {
            throw new BusinessException("Não há documento rejeitado para reenvio");
        }

        // Inativar documento anterior
        $this->kycRepository->updateDocument($existingDoc->id, [
            'status' => 'superseded'
        ]);

        // Criar novo documento
        return $this->submitDocument($kycRequestId, $documentType, $filePath, array_merge([
            'resubmission' => true,
            'previous_document_id' => $existingDoc->id
        ], $metadata));
    }

    /**
     * Obter documentos obrigatórios por tipo de KYC
     */
    private function getRequiredDocuments(string $type): array
    {
        return match ($type) {
            'basic' => ['cpf', 'rg'],
            'advanced' => ['cpf', 'rg', 'proof_of_address', 'selfie'],
            'business' => ['cnpj', 'company_registration', 'proof_of_address', 'representative_id'],
            default => ['cpf', 'rg']
        };
    }

    /**
     * Atualizar status da solicitação KYC
     */
    private function updateRequestStatus(KYCRequest $request): void
    {
        $documents = $this->kycRepository->getRequestDocuments($request->id);
        $requiredDocs = $request->required_documents;

        // Verificar se todos documentos foram enviados
        $submittedTypes = $documents->where('status', '!=', 'superseded')
                                  ->pluck('document_type')
                                  ->unique()
                                  ->toArray();

        $allSubmitted = empty(array_diff($requiredDocs, $submittedTypes));

        if (!$allSubmitted) {
            return; // Ainda aguardando documentos
        }

        // Verificar status das aprovações
        $approvedDocs = $documents->where('status', 'approved')->count();
        $rejectedDocs = $documents->where('status', 'rejected')->count();
        $pendingDocs = $documents->whereIn('status', ['submitted', 'needs_clarification'])->count();

        $newStatus = 'under_review';

        if ($pendingDocs === 0) {
            if ($rejectedDocs > 0) {
                $newStatus = 'rejected';
            } elseif ($approvedDocs === count($requiredDocs)) {
                $newStatus = 'approved';
            }
        }

        if ($request->status !== $newStatus) {
            $updateData = ['status' => $newStatus];
            
            if ($newStatus === 'approved') {
                $updateData['completed_at'] = now();
                $updateData['verification_level'] = $this->getVerificationLevel($request);
            }

            $this->kycRepository->updateRequest($request->id, $updateData);
        }
    }

    /**
     * Obter nível de verificação
     */
    private function getVerificationLevel(KYCRequest $request): string
    {
        if ($request->status !== 'approved') {
            return 'none';
        }

        return match ($request->type) {
            'basic' => 'basic',
            'advanced' => 'advanced',
            'business' => 'business',
            default => 'basic'
        };
    }

    /**
     * Obter próximas ações necessárias
     */
    private function getNextActions(KYCRequest $request, $documents): array
    {
        $actions = [];

        if ($request->status === 'pending') {
            $submittedTypes = $documents->pluck('document_type')->toArray();
            $missing = array_diff($request->required_documents, $submittedTypes);
            
            foreach ($missing as $docType) {
                $actions[] = "submit_{$docType}";
            }
        }

        if ($request->status === 'rejected') {
            $rejectedDocs = $documents->where('status', 'rejected');
            foreach ($rejectedDocs as $doc) {
                $actions[] = "resubmit_{$doc->document_type}";
            }
        }

        return $actions;
    }
}
