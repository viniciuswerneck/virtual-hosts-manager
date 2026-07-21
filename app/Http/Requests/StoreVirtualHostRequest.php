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
            'document_root' => ['required', 'string', 'regex:/^[A-Z]:[\/\\\\]/i', function ($attribute, $value, $fail) {
                $normalized = strtolower(rtrim(str_replace('\\', '/', $value), '/'));
                $blocked = ['c:/windows', 'c:/apache24', 'c:/program files', 'c:/program files (x86)', 'c:/$recycle.bin', 'c:/system volume information'];
                foreach ($blocked as $prefix) {
                    if (str_starts_with($normalized, $prefix)) {
                        $fail('O diretório raiz não pode ser um diretório do sistema.');

                        return;
                    }
                }
            }],
            'ssl_enabled' => 'boolean',
            'active' => 'boolean',
            'template' => 'nullable|string|in:laravel,wordpress,html,php',
            'php_version' => 'nullable|string|in:8.0,8.1,8.2,8.3,8.4',
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
            'document_root.regex' => 'O diretório raiz deve ser um caminho absoluto (ex: D:/www/meusite).',
        ];
    }
}
