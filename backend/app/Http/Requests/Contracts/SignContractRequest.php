<?php

namespace App\Http\Requests\Contracts;

use Illuminate\Foundation\Http\FormRequest;

class SignContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autorização será verificada no controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'signer_type' => 'required|in:buyer,seller,witness',
            'digital_signature' => 'required|string|min:64',
            'metadata' => 'sometimes|array',
            'metadata.certificate_data' => 'sometimes|string',
            'metadata.signature_method' => 'sometimes|string|in:pkcs7,simple,icp-brasil'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'signer_type.required' => 'Tipo de assinante é obrigatório',
            'signer_type.in' => 'Tipo de assinante deve ser: buyer, seller ou witness',
            'digital_signature.required' => 'Assinatura digital é obrigatória',
            'digital_signature.min' => 'Assinatura digital deve ter pelo menos 64 caracteres',
            'metadata.signature_method.in' => 'Método de assinatura deve ser: pkcs7, simple ou icp-brasil'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar formato da assinatura digital
            $signature = $this->digital_signature;
            
            if ($signature && !$this->isValidSignatureFormat($signature)) {
                $validator->errors()->add('digital_signature', 'Formato de assinatura digital inválido');
            }
        });
    }

    /**
     * Validar formato da assinatura digital
     */
    private function isValidSignatureFormat(string $signature): bool
    {
        // Verificar se é base64 válido
        if (!base64_decode($signature, true)) {
            return false;
        }

        // Verificar tamanho mínimo
        if (strlen($signature) < 64) {
            return false;
        }

        return true;
    }
}
