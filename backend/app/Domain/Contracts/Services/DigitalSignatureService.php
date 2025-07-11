<?php

namespace App\Domain\Contracts\Services;

use App\Domain\Contracts\Entities\Contract;
use App\Domain\Contracts\Entities\ContractSignature;
use App\Domain\Contracts\Repositories\ContractRepositoryInterface;
use App\Domain\Shared\Exceptions\BusinessException;
use App\Domain\Shared\Services\HashService;
use Illuminate\Support\Facades\Log;

class DigitalSignatureService
{
    public function __construct(
        private ContractRepositoryInterface $contractRepository,
        private HashService $hashService
    ) {}

    /**
     * Assinar contrato digitalmente
     */
    public function signContract(
        string $contractId,
        string $signerId,
        string $signerType,
        string $digitalSignature,
        array $metadata = []
    ): ContractSignature {
        $contract = $this->contractRepository->findById($contractId);
        
        if (!$contract) {
            throw new BusinessException("Contrato não encontrado: {$contractId}");
        }

        if (!in_array($contract->status, ['pending_signature', 'partially_signed'])) {
            throw new BusinessException("Contrato não está disponível para assinatura");
        }

        // Verificar se usuário pode assinar este contrato
        if (!$this->canUserSignContract($contract, $signerId, $signerType)) {
            throw new BusinessException("Usuário não autorizado a assinar este contrato");
        }

        // Verificar se já foi assinado por este usuário
        if ($this->hasUserSigned($contractId, $signerId)) {
            throw new BusinessException("Usuário já assinou este contrato");
        }

        // Validar assinatura digital
        $this->validateDigitalSignature($contract->content, $digitalSignature, $signerId);

        // Criar assinatura
        $signature = ContractSignature::create([
            'contract_id' => $contractId,
            'signer_id' => $signerId,
            'signer_type' => $signerType,
            'signature_hash' => $this->hashService->hash($digitalSignature),
            'signature_data' => $digitalSignature,
            'content_hash' => $this->hashService->hash($contract->content),
            'signed_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => array_merge([
                'signature_method' => 'digital',
                'timestamp' => now()->toISOString()
            ], $metadata)
        ]);

        // Atualizar status do contrato
        $this->updateContractStatus($contract);

        // Log da assinatura
        Log::info('Contract signed', [
            'contract_id' => $contractId,
            'signer_id' => $signerId,
            'signer_type' => $signerType,
            'signature_id' => $signature->id
        ]);

        return $signature;
    }

    /**
     * Verificar assinatura digital
     */
    public function verifySignature(string $signatureId): array
    {
        $signature = $this->contractRepository->findSignatureById($signatureId);
        
        if (!$signature) {
            throw new BusinessException("Assinatura não encontrada: {$signatureId}");
        }

        $contract = $this->contractRepository->findById($signature->contract_id);
        
        if (!$contract) {
            throw new BusinessException("Contrato associado não encontrado");
        }

        // Verificar integridade da assinatura
        $signatureValid = $this->hashService->verify(
            $signature->signature_data,
            $signature->signature_hash
        );

        // Verificar integridade do conteúdo
        $contentValid = $this->hashService->verify(
            $contract->content,
            $signature->content_hash
        );

        // Validar certificado digital (se aplicável)
        $certificateValid = $this->validateCertificate($signature->signature_data);

        return [
            'signature_id' => $signatureId,
            'contract_id' => $signature->contract_id,
            'signer_id' => $signature->signer_id,
            'signed_at' => $signature->signed_at,
            'validations' => [
                'signature_integrity' => $signatureValid,
                'content_integrity' => $contentValid,
                'certificate_valid' => $certificateValid,
                'overall_valid' => $signatureValid && $contentValid && $certificateValid
            ],
            'metadata' => $signature->metadata
        ];
    }

    /**
     * Obter todas as assinaturas de um contrato
     */
    public function getContractSignatures(string $contractId): array
    {
        $signatures = $this->contractRepository->getContractSignatures($contractId);
        
        return $signatures->map(function ($signature) {
            return [
                'id' => $signature->id,
                'signer_id' => $signature->signer_id,
                'signer_type' => $signature->signer_type,
                'signed_at' => $signature->signed_at,
                'ip_address' => $signature->ip_address,
                'is_valid' => $this->isSignatureValid($signature),
                'metadata' => $signature->metadata
            ];
        })->toArray();
    }

    /**
     * Invalidar assinatura
     */
    public function invalidateSignature(
        string $signatureId,
        string $reason,
        string $invalidatedBy
    ): void {
        $signature = $this->contractRepository->findSignatureById($signatureId);
        
        if (!$signature) {
            throw new BusinessException("Assinatura não encontrada: {$signatureId}");
        }

        if ($signature->invalidated_at) {
            throw new BusinessException("Assinatura já foi invalidada");
        }

        // Invalidar assinatura
        $this->contractRepository->updateSignature($signatureId, [
            'invalidated_at' => now(),
            'invalidated_by' => $invalidatedBy,
            'invalidation_reason' => $reason
        ]);

        // Atualizar status do contrato
        $contract = $this->contractRepository->findById($signature->contract_id);
        $this->updateContractStatus($contract);

        Log::warning('Contract signature invalidated', [
            'signature_id' => $signatureId,
            'contract_id' => $signature->contract_id,
            'reason' => $reason,
            'invalidated_by' => $invalidatedBy
        ]);
    }

    /**
     * Verificar se usuário pode assinar o contrato
     */
    private function canUserSignContract(Contract $contract, string $signerId, string $signerType): bool
    {
        // Comprador pode assinar
        if ($signerType === 'buyer' && $contract->buyer_id === $signerId) {
            return true;
        }

        // Vendedor pode assinar
        if ($signerType === 'seller' && $contract->seller_id === $signerId) {
            return true;
        }

        // Testemunha autorizada pode assinar
        if ($signerType === 'witness') {
            return $this->isAuthorizedWitness($contract, $signerId);
        }

        return false;
    }

    /**
     * Verificar se usuário já assinou
     */
    private function hasUserSigned(string $contractId, string $signerId): bool
    {
        $signatures = $this->contractRepository->getContractSignatures($contractId);
        
        return $signatures->where('signer_id', $signerId)
                          ->where('invalidated_at', null)
                          ->isNotEmpty();
    }

    /**
     * Validar assinatura digital
     */
    private function validateDigitalSignature(string $content, string $signature, string $signerId): void
    {
        // Implementar validação específica do formato de assinatura
        // Por ora, validação básica
        
        if (empty($signature)) {
            throw new BusinessException("Assinatura digital não pode estar vazia");
        }

        if (strlen($signature) < 64) {
            throw new BusinessException("Assinatura digital inválida - muito curta");
        }

        // Validar formato base64 (se aplicável)
        if (!base64_decode($signature, true)) {
            throw new BusinessException("Formato de assinatura digital inválido");
        }
    }

    /**
     * Atualizar status do contrato baseado nas assinaturas
     */
    private function updateContractStatus(Contract $contract): void
    {
        $signatures = $this->contractRepository->getContractSignatures($contract->id);
        $validSignatures = $signatures->where('invalidated_at', null);

        $hasBuyerSignature = $validSignatures->where('signer_id', $contract->buyer_id)->isNotEmpty();
        $hasSellerSignature = $validSignatures->where('signer_id', $contract->seller_id)->isNotEmpty();

        if ($hasBuyerSignature && $hasSellerSignature) {
            $newStatus = 'signed';
        } elseif ($hasBuyerSignature || $hasSellerSignature) {
            $newStatus = 'partially_signed';
        } else {
            $newStatus = 'pending_signature';
        }

        if ($contract->status !== $newStatus) {
            $this->contractRepository->update($contract->id, [
                'status' => $newStatus,
                'signed_at' => $newStatus === 'signed' ? now() : null
            ]);
        }
    }

    /**
     * Verificar se é testemunha autorizada
     */
    private function isAuthorizedWitness(Contract $contract, string $witnessId): bool
    {
        // Implementar lógica de autorização de testemunhas
        // Por ora, permitir qualquer usuário como testemunha
        return true;
    }

    /**
     * Validar certificado digital
     */
    private function validateCertificate(string $signature): bool
    {
        // Implementar validação de certificado ICP-Brasil quando necessário
        // Por ora, sempre válido
        return true;
    }

    /**
     * Verificar se assinatura é válida
     */
    private function isSignatureValid(ContractSignature $signature): bool
    {
        return $signature->invalidated_at === null &&
               $this->hashService->verify($signature->signature_data, $signature->signature_hash);
    }
}
