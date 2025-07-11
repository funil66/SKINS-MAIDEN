<?php

namespace App\Http\Requests\ContractTemplates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTemplateRequest extends FormRequest
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
        $templateId = $this->route('id');
        
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('contract_templates', 'name')->ignore($templateId)
            ],
            'description' => 'sometimes|string|max:1000',
            'category' => 'sometimes|string|in:skin_sale,skin_trade,service,marketplace,partnership,terms,privacy,custom',
            'content' => 'sometimes|string|min:100',
            'variables' => 'sometimes|array',
            'variables.*.name' => 'required_with:variables|string|max:100',
            'variables.*.type' => 'required_with:variables|string|in:text,number,date,email,url',
            'variables.*.required' => 'sometimes|boolean',
            'variables.*.default' => 'sometimes|string',
            'variables.*.description' => 'sometimes|string|max:500',
            'is_active' => 'sometimes|boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Já existe um template com este nome',
            'category.in' => 'Categoria deve ser uma das opções válidas',
            'content.min' => 'Conteúdo deve ter pelo menos 100 caracteres',
            'variables.*.name.required_with' => 'Nome da variável é obrigatório',
            'variables.*.type.required_with' => 'Tipo da variável é obrigatório',
            'variables.*.type.in' => 'Tipo deve ser: text, number, date, email ou url'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar apenas se ambos estão presentes
            if ($this->has('variables') && $this->has('content')) {
                $this->validateVariablesInContent($validator);
                $this->validateContentVariableFormat($validator);
            }
        });
    }

    /**
     * Validar se as variáveis declaradas estão sendo usadas no conteúdo
     */
    private function validateVariablesInContent($validator)
    {
        $content = $this->content;
        $variables = collect($this->variables);

        foreach ($variables as $index => $variable) {
            $varName = $variable['name'] ?? '';
            $placeholder = '{{' . $varName . '}}';

            if (!str_contains($content, $placeholder)) {
                $validator->errors()->add(
                    "variables.{$index}.name",
                    "Variável '{$varName}' não está sendo usada no conteúdo"
                );
            }
        }
    }

    /**
     * Validar formato das variáveis no conteúdo
     */
    private function validateContentVariableFormat($validator)
    {
        $content = $this->content;
        
        // Encontrar todas as variáveis no formato {{variavel}}
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        
        if (isset($matches[1])) {
            $usedVariables = $matches[1];
            $declaredVariables = collect($this->variables ?? [])->pluck('name')->toArray();
            
            foreach ($usedVariables as $usedVar) {
                if (!in_array($usedVar, $declaredVariables)) {
                    $validator->errors()->add(
                        'content',
                        "Variável '{$usedVar}' usada no conteúdo não foi declarada"
                    );
                }
            }
        }
    }
}
