<?php

namespace App\Domain\Contracts\Services;

use App\Domain\Contracts\Entities\Contract;
use App\Domain\Contracts\Entities\ContractTemplate;
use App\Domain\Contracts\Repositories\ContractRepositoryInterface;
use App\Domain\Contracts\Repositories\ContractTemplateRepositoryInterface;
use App\Domain\Shared\Exceptions\BusinessException;

class ContractGenerationService
{
    public function __construct(
        private ContractRepositoryInterface $contractRepository,
        private ContractTemplateRepositoryInterface $templateRepository
    ) {}

    /**
     * Gerar contrato a partir de template
     */
    public function generateFromTemplate(
        string $templateId,
        string $transactionId,
        string $buyerId,
        string $sellerId,
        array $variables = []
    ): Contract {
        $template = $this->templateRepository->findById($templateId);
        
        if (!$template) {
            throw new BusinessException("Template não encontrado: {$templateId}");
        }

        if (!$template->is_active) {
            throw new BusinessException("Template não está ativo: {$templateId}");
        }

        // Validar variáveis obrigatórias
        $missingVariables = $template->validateVariables($variables);
        if (!empty($missingVariables)) {
            throw new BusinessException(
                "Variáveis obrigatórias ausentes: " . implode(', ', $missingVariables)
            );
        }

        // Adicionar variáveis padrão
        $defaultVariables = $this->getDefaultVariables($transactionId, $buyerId, $sellerId);
        $allVariables = array_merge($defaultVariables, $variables);

        // Gerar conteúdo do contrato
        $content = $template->generateContract($allVariables);

        // Criar contrato
        return $this->contractRepository->create([
            'transaction_id' => $transactionId,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'template_id' => $templateId,
            'content' => $content,
            'status' => 'draft',
            'metadata' => [
                'template_version' => $template->version,
                'generated_at' => now()->toISOString(),
                'variables_used' => $allVariables
            ]
        ]);
    }

    /**
     * Gerar contrato personalizado
     */
    public function generateCustomContract(
        string $transactionId,
        string $buyerId,
        string $sellerId,
        string $content,
        array $metadata = []
    ): Contract {
        return $this->contractRepository->create([
            'transaction_id' => $transactionId,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'template_id' => null,
            'content' => $content,
            'status' => 'draft',
            'metadata' => array_merge([
                'type' => 'custom',
                'generated_at' => now()->toISOString()
            ], $metadata)
        ]);
    }

    /**
     * Finalizar contrato para assinatura
     */
    public function finalizeForSignature(string $contractId): Contract
    {
        $contract = $this->contractRepository->findById($contractId);
        
        if (!$contract) {
            throw new BusinessException("Contrato não encontrado: {$contractId}");
        }

        if ($contract->status !== 'draft') {
            throw new BusinessException("Contrato não está em status de rascunho");
        }

        // Validar conteúdo do contrato
        $this->validateContractContent($contract->content);

        // Atualizar status
        $this->contractRepository->update($contractId, [
            'status' => 'pending_signature'
        ]);

        return $this->contractRepository->findById($contractId);
    }

    /**
     * Criar template a partir de contrato
     */
    public function createTemplateFromContract(
        string $contractId,
        string $templateName,
        string $category,
        array $variables = []
    ): ContractTemplate {
        $contract = $this->contractRepository->findById($contractId);
        
        if (!$contract) {
            throw new BusinessException("Contrato não encontrado: {$contractId}");
        }

        // Converter conteúdo para template
        $templateContent = $this->convertToTemplate($contract->content, $variables);

        return $this->templateRepository->create([
            'name' => $templateName,
            'category' => $category,
            'content' => $templateContent,
            'variables' => $variables,
            'version' => '1.0',
            'is_active' => true,
            'created_by' => auth()->id()
        ]);
    }

    /**
     * Obter variáveis padrão do sistema
     */
    private function getDefaultVariables(string $transactionId, string $buyerId, string $sellerId): array
    {
        return [
            'transaction_id' => $transactionId,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'generated_date' => now()->format('d/m/Y'),
            'generated_time' => now()->format('H:i:s'),
            'contract_id' => \Illuminate\Support\Str::uuid(),
            'platform_name' => config('app.name'),
            'support_email' => config('mail.support_address', 'suporte@ironcodeskins.com')
        ];
    }

    /**
     * Validar conteúdo do contrato
     */
    private function validateContractContent(string $content): void
    {
        // Verificar se contém seções obrigatórias
        $requiredSections = [
            'partes envolvidas',
            'objeto do contrato',
            'obrigações',
            'prazo',
            'foro'
        ];

        $contentLower = strtolower($content);
        
        foreach ($requiredSections as $section) {
            if (strpos($contentLower, $section) === false) {
                throw new BusinessException("Seção obrigatória ausente: {$section}");
            }
        }

        // Verificar tamanho mínimo
        if (strlen($content) < 500) {
            throw new BusinessException("Conteúdo do contrato muito curto (mínimo 500 caracteres)");
        }
    }

    /**
     * Converter conteúdo para template
     */
    private function convertToTemplate(string $content, array $variables): string
    {
        $templateContent = $content;
        
        foreach ($variables as $variable) {
            $placeholder = '{{' . $variable['name'] . '}}';
            // Este é um processo manual que requer revisão humana
            // Por ora, retornamos o conteúdo original
        }
        
        return $templateContent;
    }
}
