<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KYCDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kyc_request_id' => $this->kycRequestId,
            'document_type' => $this->documentType,
            'file_name' => $this->fileName,
            'file_size' => $this->fileSize,
            'mime_type' => $this->mimeType,
            'status' => $this->status,
            'reviewed_at' => $this->reviewedAt?->toISOString(),
            'reviewer_id' => $this->reviewerId,
            'created_at' => $this->createdAt->toISOString(),
            
            // Campos computados
            'status_display' => $this->getStatusDisplay(),
            'document_type_display' => $this->getDocumentTypeDisplay(),
            'file_size_formatted' => $this->getFileSizeFormatted(),
            
            // Dados sensíveis apenas para admins
            'document_hash' => $this->when(
                $request->user()?->hasRole('admin'),
                $this->documentHash
            ),
            
            'metadata' => $this->when(
                $request->user()?->hasRole('admin'),
                $this->metadata
            )
        ];
    }
    
    private function getStatusDisplay(): string
    {
        return match ($this->status) {
            'submitted' => 'Enviado',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            'needs_clarification' => 'Necessita Esclarecimento',
            'superseded' => 'Substituído',
            default => 'Status Desconhecido'
        };
    }
    
    private function getDocumentTypeDisplay(): string
    {
        return match ($this->documentType) {
            'cpf' => 'CPF',
            'rg' => 'RG',
            'cnh' => 'CNH',
            'passport' => 'Passaporte',
            'proof_of_address' => 'Comprovante de Endereço',
            'selfie' => 'Selfie',
            'cnpj' => 'CNPJ',
            'company_registration' => 'Registro da Empresa',
            'representative_id' => 'Documento do Representante',
            default => 'Documento Personalizado'
        ];
    }
    
    private function getFileSizeFormatted(): string
    {
        $bytes = $this->fileSize;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
}
