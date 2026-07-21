@extends('layouts.app')

@section('title', __('Clonar') . ' ' . $original->server_name)

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">
            <i class="fas fa-clone text-gray-400 mr-2"></i>{{ __('Clonar Virtual Host') }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Clonar') }} {{ $original->server_name }}</p>
    </div>

    <div class="card p-6 max-w-xl">
        <form action="{{ route('virtual-hosts.clone-store', $original) }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="label" for="server_name"><i class="fas fa-server mr-1.5 text-gray-400"></i>{{ __('Novo Nome do Servidor') }}</label>
                    <input type="text" name="server_name" id="server_name" value="{{ old('server_name') }}"
                           class="input {{ $errors->has('server_name') ? 'input-error' : '' }}"
                           placeholder="copia-{{ $original->server_name }}" required autofocus>
                    @error('server_name')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label" for="document_root"><i class="fas fa-folder mr-1.5 text-gray-400"></i>{{ __('Diretório Raiz') }}</label>
                    <input type="text" name="document_root" id="document_root"
                           value="{{ old('document_root', $original->document_root) }}"
                           class="input">
                </div>

                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 text-sm">
                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Configurações que serão copiadas:') }}</p>
                    <ul class="space-y-1 text-gray-500 dark:text-gray-400">
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>{{ __('Porta') }}: {{ $original->port }}</li>
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>{{ __('SSL') }}: {{ $original->ssl_enabled ? 'HTTPS' : 'HTTP' }}</li>
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>{{ __('PHP') }}: {{ $original->php_version ?: __('Padrão') }}</li>
                        @if ($original->server_aliases)
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>{{ __('Aliases') }}: {{ $original->server_aliases }}</li>
                        @endif
                        @if ($original->github_url)
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>{{ __('GitHub') }}: {{ $original->github_url }}</li>
                        @endif
                    </ul>
                    <p class="text-xs text-gray-400 mt-3"><i class="fas fa-info-circle mr-1"></i>{{ __('O novo vhost será criado como inativo. Ative-o após verificar a configuração.') }}</p>
                </div>
            </div>

            <div class="flex gap-2 mt-6">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-clone"></i> {{ __('Clonar') }}
                </button>
                <a href="{{ route('virtual-hosts.index') }}" class="btn-ghost">
                    {{ __('Cancelar') }}
                </a>
            </div>
        </form>
    </div>
@endsection
