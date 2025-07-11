<?php

namespace App\Domain\Compliance\Services;

use App\Domain\Compliance\Entities\ConsentRecord;
use App\Domain\Compliance\Entities\DataProcessingLog;
use App\Domain\Compliance\Entities\PrivacyRequest;
use App\Domain\Compliance\Repositories\ComplianceRepositoryInterface;
use App\Domain\Shared\Exceptions\BusinessException;
use Illuminate\Support\Facades\Log;

class LGPDComplianceService
{
    public function __construct(
        private ComplianceRepositoryInterface $complianceRepository
    ) {}

    /**
     * Registrar consentimento do usuário
     */
    public function recordConsent(
        string $userId,
        string $purpose,
        string $legalBasis,
        array $dataTypes,
        bool $granted = true,
        array $metadata = []
    ): ConsentRecord {
        // Verificar se já existe consentimento para esta finalidade
        $existingConsent = $this->complianceRepository->getActiveConsent($userId, $purpose);
        
        if ($existingConsent) {
            // Revogar consentimento anterior se necessário
            if ($granted !== $existingConsent->granted) {
                $this->revokeConsent($existingConsent->id, 'Updated consent');
            } else {
                throw new BusinessException("Consentimento já registrado para esta finalidade");
            }
        }

        // Validar base legal
        $this->validateLegalBasis($legalBasis, $purpose);

        // Criar registro de consentimento
        $consent = $this->complianceRepository->createConsent([
            'user_id' => $userId,
            'purpose' => $purpose,
            'legal_basis' => $legalBasis,
            'data_types' => $dataTypes,
            'granted' => $granted,
            'granted_at' => $granted ? now() : null,
            'expires_at' => $this->calculateExpirationDate($purpose),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => array_merge([
                'consent_version' => '1.0',
                'timestamp' => now()->toISOString()
            ], $metadata)
        ]);

        // Log da ação
        $this->logDataProcessing(
            $userId,
            'consent_recorded',
            [
                'purpose' => $purpose,
                'legal_basis' => $legalBasis,
                'granted' => $granted,
                'consent_id' => $consent->id
            ]
        );

        Log::info('LGPD consent recorded', [
            'user_id' => $userId,
            'purpose' => $purpose,
            'granted' => $granted,
            'consent_id' => $consent->id
        ]);

        return $consent;
    }

    /**
     * Revogar consentimento
     */
    public function revokeConsent(string $consentId, string $reason = null): void
    {
        $consent = $this->complianceRepository->getConsentById($consentId);
        
        if (!$consent) {
            throw new BusinessException("Consentimento não encontrado: {$consentId}");
        }

        if ($consent->revoked_at) {
            throw new BusinessException("Consentimento já foi revogado");
        }

        // Revogar consentimento
        $this->complianceRepository->updateConsent($consentId, [
            'revoked_at' => now(),
            'revocation_reason' => $reason
        ]);

        // Log da revogação
        $this->logDataProcessing(
            $consent->user_id,
            'consent_revoked',
            [
                'consent_id' => $consentId,
                'reason' => $reason,
                'purpose' => $consent->purpose
            ]
        );

        Log::info('LGPD consent revoked', [
            'consent_id' => $consentId,
            'user_id' => $consent->user_id,
            'reason' => $reason
        ]);
    }

    /**
     * Processar solicitação de exercício de direitos
     */
    public function processPrivacyRequest(
        string $userId,
        string $requestType,
        string $description = null,
        array $metadata = []
    ): PrivacyRequest {
        // Validar tipo de solicitação
        $validTypes = ['access', 'rectification', 'erasure', 'portability', 'restriction', 'objection'];
        
        if (!in_array($requestType, $validTypes)) {
            throw new BusinessException("Tipo de solicitação inválido: {$requestType}");
        }

        // Verificar se há solicitação pendente do mesmo tipo
        $pendingRequest = $this->complianceRepository->getPendingPrivacyRequest($userId, $requestType);
        
        if ($pendingRequest) {
            throw new BusinessException("Já existe solicitação pendente deste tipo");
        }

        // Criar solicitação
        $request = $this->complianceRepository->createPrivacyRequest([
            'user_id' => $userId,
            'request_type' => $requestType,
            'description' => $description,
            'status' => 'pending',
            'requested_at' => now(),
            'deadline' => $this->calculateRequestDeadline($requestType),
            'metadata' => array_merge([
                'request_timestamp' => now()->toISOString(),
                'ip_address' => request()->ip()
            ], $metadata)
        ]);

        // Log da solicitação
        $this->logDataProcessing(
            $userId,
            'privacy_request_created',
            [
                'request_type' => $requestType,
                'request_id' => $request->id,
                'description' => $description
            ]
        );

        // Enviar notificação para equipe de compliance
        $this->notifyComplianceTeam($request);

        Log::info('LGPD privacy request created', [
            'user_id' => $userId,
            'request_type' => $requestType,
            'request_id' => $request->id
        ]);

        return $request;
    }

    /**
     * Responder solicitação de privacidade
     */
    public function respondToPrivacyRequest(
        string $requestId,
        string $response,
        string $respondedBy,
        array $attachments = [],
        array $metadata = []
    ): PrivacyRequest {
        $request = $this->complianceRepository->getPrivacyRequestById($requestId);
        
        if (!$request) {
            throw new BusinessException("Solicitação não encontrada: {$requestId}");
        }

        if ($request->status !== 'pending') {
            throw new BusinessException("Solicitação não está em status pendente");
        }

        // Atualizar solicitação
        $updatedRequest = $this->complianceRepository->updatePrivacyRequest($requestId, [
            'status' => 'completed',
            'response' => $response,
            'responded_by' => $respondedBy,
            'responded_at' => now(),
            'attachments' => $attachments,
            'metadata' => array_merge($request->metadata ?? [], $metadata)
        ]);

        // Log da resposta
        $this->logDataProcessing(
            $request->user_id,
            'privacy_request_responded',
            [
                'request_id' => $requestId,
                'request_type' => $request->request_type,
                'responded_by' => $respondedBy
            ]
        );

        Log::info('LGPD privacy request responded', [
            'request_id' => $requestId,
            'user_id' => $request->user_id,
            'responded_by' => $respondedBy
        ]);

        return $updatedRequest;
    }

    /**
     * Obter relatório de conformidade do usuário
     */
    public function getUserComplianceReport(string $userId): array
    {
        $consents = $this->complianceRepository->getUserConsents($userId);
        $privacyRequests = $this->complianceRepository->getUserPrivacyRequests($userId);
        $processingLogs = $this->complianceRepository->getUserProcessingLogs($userId, 30); // Últimos 30 dias

        return [
            'user_id' => $userId,
            'report_generated_at' => now()->toISOString(),
            'consents' => [
                'total' => $consents->count(),
                'active' => $consents->where('revoked_at', null)->count(),
                'revoked' => $consents->whereNotNull('revoked_at')->count(),
                'details' => $consents->map(function ($consent) {
                    return [
                        'purpose' => $consent->purpose,
                        'legal_basis' => $consent->legal_basis,
                        'granted' => $consent->granted,
                        'granted_at' => $consent->granted_at,
                        'revoked_at' => $consent->revoked_at,
                        'status' => $consent->revoked_at ? 'revoked' : 'active'
                    ];
                })->toArray()
            ],
            'privacy_requests' => [
                'total' => $privacyRequests->count(),
                'pending' => $privacyRequests->where('status', 'pending')->count(),
                'completed' => $privacyRequests->where('status', 'completed')->count(),
                'details' => $privacyRequests->map(function ($request) {
                    return [
                        'type' => $request->request_type,
                        'status' => $request->status,
                        'requested_at' => $request->requested_at,
                        'responded_at' => $request->responded_at,
                        'deadline' => $request->deadline
                    ];
                })->toArray()
            ],
            'processing_activity' => [
                'last_30_days' => $processingLogs->count(),
                'types' => $processingLogs->groupBy('action_type')->map(function ($logs) {
                    return $logs->count();
                })->toArray()
            ]
        ];
    }

    /**
     * Log de processamento de dados
     */
    public function logDataProcessing(
        string $userId,
        string $actionType,
        array $details = [],
        string $legalBasis = null
    ): DataProcessingLog {
        return $this->complianceRepository->createProcessingLog([
            'user_id' => $userId,
            'action_type' => $actionType,
            'legal_basis' => $legalBasis,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()
        ]);
    }

    /**
     * Validar base legal
     */
    private function validateLegalBasis(string $legalBasis, string $purpose): void
    {
        $validBases = [
            'consent',
            'contract',
            'legal_obligation',
            'vital_interests',
            'public_task',
            'legitimate_interests'
        ];

        if (!in_array($legalBasis, $validBases)) {
            throw new BusinessException("Base legal inválida: {$legalBasis}");
        }

        // Validações específicas por finalidade
        if ($purpose === 'marketing' && $legalBasis !== 'consent') {
            throw new BusinessException("Marketing requer consentimento explícito");
        }
    }

    /**
     * Calcular data de expiração do consentimento
     */
    private function calculateExpirationDate(string $purpose): ?\Carbon\Carbon
    {
        return match ($purpose) {
            'marketing' => now()->addYear(),
            'analytics' => now()->addMonths(6),
            'essential' => null, // Não expira
            default => now()->addYear()
        };
    }

    /**
     * Calcular prazo para resposta da solicitação
     */
    private function calculateRequestDeadline(string $requestType): \Carbon\Carbon
    {
        // LGPD estabelece 15 dias úteis
        return now()->addWeekdays(15);
    }

    /**
     * Notificar equipe de compliance
     */
    private function notifyComplianceTeam(PrivacyRequest $request): void
    {
        // Implementar notificação por email/sistema
        Log::info('Compliance team notification', [
            'request_id' => $request->id,
            'request_type' => $request->request_type,
            'user_id' => $request->user_id
        ]);
    }
}
