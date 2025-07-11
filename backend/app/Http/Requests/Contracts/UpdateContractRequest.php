<?php

namespace App\Http\Requests\Contracts;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
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
            'content' => 'sometimes|string|min:100',
            'metadata' => 'sometimes|array',
            'expires_at' => 'sometimes|date|after:now'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'content.min' => 'Conteúdo deve ter pelo menos 100 caracteres',
            'expires_at.date' => 'Data de expiração deve ser uma data válida',
            'expires_at.after' => 'Data de expiração deve ser futura'
        ];
    }
}
