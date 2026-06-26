<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVirtualHostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('document_root')) {
            $this->merge([
                'document_root' => trim(str_replace('\\', '/', $this->input('document_root'))),
            ]);
        }
    }

    public function rules(): array
    {
        $virtualHost = $this->route('virtual_host');

        return [
            'server_name' => [
                'required',
                'regex:/^[a-z0-9]([a-z0-9.-]*[a-z0-9])?$/',
                Rule::unique('virtual_hosts', 'server_name')->ignore($virtualHost?->id),
            ],
            'document_root' => 'required|string',
            'ssl_enabled' => 'boolean',
            'active' => 'boolean',
            'template' => 'nullable|string|in:laravel,wordpress,html,php',
            'port' => 'integer|min:1|max:65535',
            'notes' => 'nullable|string',
            'github_url' => 'nullable|url',
        ];
    }

    public function messages(): array
    {
        return [
            'server_name.required' => 'O nome do servidor é obrigatório.',
            'server_name.regex' => 'O nome do servidor deve ser um domínio válido (ex: meusite.local).',
            'server_name.unique' => 'Este nome de servidor já está cadastrado.',
            'document_root.required' => 'O diretório raiz é obrigatório.',
        ];
    }
}
