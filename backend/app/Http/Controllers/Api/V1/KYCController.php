<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domain\KYC\Services\KYCVerificationService;
use App\Domain\KYC\Repositories\KYCRepositoryInterface;
use App\Http\Requests\KYC\InitiateKYCRequest;
use App\Http\Requests\KYC\SubmitDocumentRequest;
use App\Http\Requests\KYC\ReviewDocumentRequest;
use App\Http\Resources\KYCRequestResource;
use App\Http\Resources\KYCDocumentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KYCController extends Controller
{
    public function __construct(
        private KYCVerificationService $kycService,
        private KYCRepositoryInterface $kycRepository
    ) {}

    /**
     * Iniciar processo KYC
     */
    public function initiate(InitiateKYCRequest $request): JsonResponse
    {
        try {
            $kycRequest = $this->kycService->initiateKYC(
                $request->user()->id,
                $request->type ?? 'basic',
                $request->metadata ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Processo KYC iniciado com sucesso',
                'data' => new KYCRequestResource($kycRequest)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar KYC: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Enviar documento
     */
    public function submitDocument(SubmitDocumentRequest $request, string $kycRequestId): JsonResponse
    {
        try {
            $document = $this->kycService->submitDocument(
                $kycRequestId,
                $request->document_type,
                $request->file('document')->path(),
                $request->metadata ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Documento enviado com sucesso',
                'data' => new KYCDocumentResource($document)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar documento: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Revisar documento (admin only)
     */
    public function reviewDocument(ReviewDocumentRequest $request, string $documentId): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $review = $this->kycService->reviewDocument(
                $documentId,
                $request->user()->id,
                $request->decision,
                $request->comments,
                $request->metadata ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Documento revisado com sucesso',
                'data' => $review
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao revisar documento: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Obter status KYC do usuário
     */
    public function status(Request $request): JsonResponse
    {
        $status = $this->kycService->getUserKYCStatus($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }

    /**
     * Reenviar documento rejeitado
     */
    public function resubmitDocument(SubmitDocumentRequest $request, string $kycRequestId): JsonResponse
    {
        try {
            $document = $this->kycService->resubmitDocument(
                $kycRequestId,
                $request->document_type,
                $request->file('document')->path(),
                $request->metadata ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Documento reenviado com sucesso',
                'data' => new KYCDocumentResource($document)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao reenviar documento: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Listar solicitações pendentes (admin only)
     */
    public function pending(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], Response::HTTP_FORBIDDEN);
        }

        $requests = $this->kycRepository->getPendingRequests(
            $request->get('page', 1),
            $request->get('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => KYCRequestResource::collection($requests->items()),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
                'last_page' => $requests->lastPage()
            ]
        ]);
    }

    /**
     * Obter estatísticas KYC (admin only)
     */
    public function statistics(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], Response::HTTP_FORBIDDEN);
        }

        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to')
        ];

        $stats = $this->kycRepository->getStatistics(array_filter($filters));

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Obter detalhes da solicitação KYC
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $kycRequest = $this->kycRepository->getRequestById($id);

        if (!$kycRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitação KYC não encontrada'
            ], Response::HTTP_NOT_FOUND);
        }

        // Verificar permissão
        if (!$request->user()->hasRole('admin') && $kycRequest->userId !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'data' => new KYCRequestResource($kycRequest)
        ]);
    }
}
