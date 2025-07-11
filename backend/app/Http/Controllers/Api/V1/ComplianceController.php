<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domain\Compliance\Services\LGPDComplianceService;
use App\Domain\Compliance\Repositories\ComplianceRepositoryInterface;
use App\Http\Requests\Compliance\RecordConsentRequest;
use App\Http\Requests\Compliance\PrivacyRequestRequest;
use App\Http\Requests\Compliance\RespondPrivacyRequestRequest;
use App\Http\Resources\ConsentRecordResource;
use App\Http\Resources\PrivacyRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ComplianceController extends Controller
{
    public function __construct(
        private LGPDComplianceService $complianceService,
        private ComplianceRepositoryInterface $complianceRepository
    ) {}

    /**
     * Registrar consentimento
     */
    public function recordConsent(RecordConsentRequest $request): JsonResponse
    {
        try {
            $consent = $this->complianceService->recordConsent(
                $request->user()->id,
                $request->purpose,
                $request->legal_basis,
                $request->data_types,
                $request->granted ?? true,
                $request->metadata ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Consentimento registrado com sucesso',
                'data' => new ConsentRecordResource($consent)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar consentimento: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Revogar consentimento
     */
    public function revokeConsent(Request $request, string $consentId): JsonResponse
    {
        try {
            $this->complianceService->revokeConsent(
                $consentId,
                $request->get('reason', 'Revogado pelo usuário')
            );

            return response()->json([
                'success' => true,
                'message' => 'Consentimento revogado com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao revogar consentimento: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Criar solicitação de privacidade
     */
    public function createPrivacyRequest(PrivacyRequestRequest $request): JsonResponse
    {
        try {
            $privacyRequest = $this->complianceService->processPrivacyRequest(
                $request->user()->id,
                $request->request_type,
                $request->description,
                $request->metadata ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Solicitação de privacidade criada com sucesso',
                'data' => new PrivacyRequestResource($privacyRequest)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar solicitação: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Responder solicitação de privacidade (admin only)
     */
    public function respondPrivacyRequest(RespondPrivacyRequestRequest $request, string $requestId): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $privacyRequest = $this->complianceService->respondToPrivacyRequest(
                $requestId,
                $request->response,
                $request->user()->id,
                $request->attachments ?? [],
                $request->metadata ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Solicitação respondida com sucesso',
                'data' => new PrivacyRequestResource($privacyRequest)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao responder solicitação: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Obter relatório de conformidade do usuário
     */
    public function userReport(Request $request): JsonResponse
    {
        $report = $this->complianceService->getUserComplianceReport($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }

    /**
     * Listar consentimentos do usuário
     */
    public function userConsents(Request $request): JsonResponse
    {
        $consents = $this->complianceRepository->getUserConsents($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => ConsentRecordResource::collection($consents)
        ]);
    }

    /**
     * Listar solicitações de privacidade do usuário
     */
    public function userPrivacyRequests(Request $request): JsonResponse
    {
        $requests = $this->complianceRepository->getUserPrivacyRequests($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => PrivacyRequestResource::collection($requests)
        ]);
    }

    /**
     * Listar solicitações pendentes (admin only)
     */
    public function pendingRequests(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], Response::HTTP_FORBIDDEN);
        }

        $requests = $this->complianceRepository->getPendingPrivacyRequests(
            $request->get('page', 1),
            $request->get('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => PrivacyRequestResource::collection($requests->items()),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
                'last_page' => $requests->lastPage()
            ]
        ]);
    }

    /**
     * Obter estatísticas de compliance (admin only)
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

        $stats = $this->complianceRepository->getComplianceStatistics(array_filter($filters));

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Exportar dados do usuário (LGPD Art. 15)
     */
    public function exportUserData(Request $request): JsonResponse
    {
        // Esta funcionalidade seria expandida para incluir todos os dados do usuário
        $report = $this->complianceService->getUserComplianceReport($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Dados exportados com sucesso',
            'data' => [
                'export_date' => now()->toISOString(),
                'user_id' => $request->user()->id,
                'compliance_report' => $report
            ]
        ]);
    }

    /**
     * Portal de privacidade
     */
    public function privacyPortal(Request $request): JsonResponse
    {
        $consents = $this->complianceRepository->getUserConsents($request->user()->id);
        $privacyRequests = $this->complianceRepository->getUserPrivacyRequests($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $request->user()->id,
                    'email' => $request->user()->email,
                    'name' => $request->user()->name
                ],
                'consents' => ConsentRecordResource::collection($consents),
                'privacy_requests' => PrivacyRequestResource::collection($privacyRequests),
                'available_actions' => [
                    'export_data',
                    'request_deletion',
                    'request_rectification',
                    'revoke_consents'
                ],
                'rights_information' => [
                    'access' => 'Direito de acesso aos seus dados pessoais',
                    'rectification' => 'Direito de correção de dados incompletos ou incorretos',
                    'erasure' => 'Direito de exclusão dos seus dados pessoais',
                    'portability' => 'Direito de portabilidade dos dados',
                    'objection' => 'Direito de oposição ao tratamento'
                ]
            ]
        ]);
    }
}
