<?php

namespace App\Http\Requests\Contracts;

use Illuminate\Foundation\Http\FormRequest;

class CreateContractRequest extends FormRequest
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
        $rules = [
            'transaction_id' => 'required|string|max:255',
            'buyer_id' => 'required|uuid|exists:users,id',
            'seller_id' => 'required|uuid|exists:users,id|different:buyer_id',
            'metadata' => 'sometimes|array'
        ];

        // Validação condicional: template ou conteúdo personalizado
        if ($this->has('template_id')) {
            $rules['template_id'] = 'required|uuid|exists:contract_templates,id';
            $rules['variables'] = 'sometimes|array';
        } else {
            $rules['content'] = 'required|string|min:100';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'transaction_id.required' => 'ID da transação é obrigatório',
            'buyer_id.required' => 'ID do comprador é obrigatório',
            'buyer_id.exists' => 'Comprador não encontrado',
            'seller_id.required' => 'ID do vendedor é obrigatório',
            'seller_id.exists' => 'Vendedor não encontrado',
            'seller_id.different' => 'Comprador e vendedor devem ser diferentes',
            'template_id.exists' => 'Template de contrato não encontrado',
            'content.required' => 'Conteúdo do contrato é obrigatório',
            'content.min' => 'Conteúdo deve ter pelo menos 100 caracteres'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verificar se o template está ativo (se fornecido)
            if ($this->template_id) {
                $template = \App\Models\ContractTemplate::find($this->template_id);
                if ($template && !$template->is_active) {
                    $validator->errors()->add('template_id', 'Template não está ativo');
                }
            }

            // Verificar se é um dos dois: template_id ou content
            if (!$this->template_id && !$this->content) {
                $validator->errors()->add('template_id', 'Forneça um template_id ou content personalizado');
            }
        });
    }
}
