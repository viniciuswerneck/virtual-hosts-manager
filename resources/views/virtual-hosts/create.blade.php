@extends('layouts.app')

@section('title', __('Novo Virtual Host'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight"><i class="fas fa-plus-circle text-blue-500 mr-2"></i>{{ __('Novo Virtual Host') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Cria o hosts, configuração do Apache e certificado SSL automaticamente.') }}</p>
    </div>

    <div class="card p-6 max-w-2xl">
        <form action="{{ route('virtual-hosts.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="label" for="server_name"><i class="fas fa-server mr-1.5 text-gray-400"></i>{{ __('Nome do Servidor') }}</label>
                <input type="text" name="server_name" id="server_name" value="{{ old('server_name') }}"
                       class="input {{ $errors->has('server_name') ? 'input-error' : '' }}"
                       placeholder="meusite.local">
                @error('server_name')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400 mt-1.5">{{ __('Ex:') }} meusite.local, projeto.test</p>
            </div>

            <div>
                <label class="label" for="server_aliases"><i class="fas fa-list mr-1.5 text-gray-400"></i>{{ __('Aliases do Servidor') }}</label>
                <input type="text" name="server_aliases" id="server_aliases" value="{{ old('server_aliases') }}"
                       class="input {{ $errors->has('server_aliases') ? 'input-error' : '' }}"
                       placeholder="www.meusite.local, meusite.dev">
                <p class="text-xs text-gray-400 mt-1.5">{{ __('Separados por vírgula. Ex:') }} www.meusite.local, meusite.dev</p>
            </div>

            <div>
                <label class="label" for="document_root"><i class="fas fa-folder mr-1.5 text-gray-400"></i>{{ __('Diretório Raiz') }}</label>
                <div class="flex gap-2">
                    <input type="text" name="document_root" id="document_root" value="{{ old('document_root', config('virtualhosts.default_document_root')) }}"
                           class="input flex-1 {{ $errors->has('document_root') ? 'input-error' : '' }}"
                           placeholder="D:/www/meusite/public">
                    <button type="button" onclick="openExplorer()" class="btn-secondary" title="{{ __('Abrir no Explorer') }}">
                        <i class="fas fa-folder-open"></i>
                    </button>
                </div>
                @error('document_root')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label" for="port"><i class="fas fa-plug mr-1.5 text-gray-400"></i>{{ __('Porta') }}</label>
                <div class="flex gap-2 items-center">
                    <input type="number" name="port" id="port" value="{{ old('port', 80) }}"
                           class="input {{ $errors->has('port') ? 'input-error' : '' }}"
                           onblur="checkPort(this.value)">
                    <span id="port-status" class="text-xs hidden"></span>
                </div>
                @error('port')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label" for="template"><i class="fas fa-magic mr-1.5 text-gray-400"></i>{{ __('Template do Projeto') }}</label>
                <select name="template" id="template" class="input">
                    <option value="" {{ !old('template') ? 'selected' : '' }}>{{ __('Nenhum (apenas criar vhost)') }}</option>
                    <option value="laravel" {{ old('template') === 'laravel' ? 'selected' : '' }}>{{ __('Laravel (composer create-project)') }}</option>
                    <option value="wordpress" {{ old('template') === 'wordpress' ? 'selected' : '' }}>{{ __('WordPress (download + extract)') }}</option>
                    <option value="html" {{ old('template') === 'html' ? 'selected' : '' }}>{{ __('HTML + Tailwind (estático)') }}</option>
                    <option value="php" {{ old('template') === 'php' ? 'selected' : '' }}>{{ __('PHP Puro (index.php + config.php)') }}</option>
                </select>
                <p class="text-xs text-gray-400 mt-1.5">{{ __('Escolha um template para iniciar o projeto automaticamente no diretório. Se preencher o GitHub, o template é ignorado.') }}</p>
            </div>

            <div>
                <label class="label" for="php_version"><i class="fab fa-php mr-1.5 text-gray-400"></i>{{ __('Versão do PHP') }}</label>
                <select name="php_version" id="php_version" class="input">
                    <option value="" {{ !old('php_version') ? 'selected' : '' }}>{{ __('Padrão do Apache') }}</option>
                    <option value="8.0" {{ old('php_version') === '8.0' ? 'selected' : '' }}>{{ __('PHP 8.0') }}</option>
                    <option value="8.1" {{ old('php_version') === '8.1' ? 'selected' : '' }}>{{ __('PHP 8.1') }}</option>
                    <option value="8.2" {{ old('php_version') === '8.2' ? 'selected' : '' }}>{{ __('PHP 8.2') }}</option>
                    <option value="8.3" {{ old('php_version') === '8.3' ? 'selected' : '' }}>{{ __('PHP 8.3') }}</option>
                    <option value="8.4" {{ old('php_version') === '8.4' ? 'selected' : '' }}>{{ __('PHP 8.4') }}</option>
                </select>
                <p class="text-xs text-gray-400 mt-1.5">{{ __('Define o handler FCGID no Apache. Deixe como "Padrão" para usar a versão configurada globalmente.') }}</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="this.previousElementSibling.previousElementSibling.click()"
                        class="toggle {{ old('ssl_enabled', true) ? 'toggle-on' : 'toggle-off' }}">
                    <span class="toggle-dot"></span>
                </button>
                <input type="hidden" name="ssl_enabled" value="0">
                <input type="checkbox" name="ssl_enabled" id="ssl_enabled" value="1"
                       {{ old('ssl_enabled', true) ? 'checked' : '' }}
                       class="hidden" onchange="this.parentElement.querySelector('.toggle').className = 'toggle ' + (this.checked ? 'toggle-on' : 'toggle-off')">
                <label for="ssl_enabled" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer select-none"><i class="fas fa-lock mr-1.5 text-gray-400"></i>{{ __('Habilitar SSL (HTTPS)') }}</label>
            </div>
            <p class="text-xs text-gray-400 mt-1.5 ml-11">{{ __('Gera certificado via mkcert automaticamente.') }}</p>

            <div>
                <label class="label" for="github_url"><i class="fab fa-github mr-1.5 text-gray-400"></i>{{ __('GitHub (clonar repositório)') }}</label>
                <input type="url" name="github_url" id="github_url" value="{{ old('github_url') }}"
                       class="input {{ $errors->has('github_url') ? 'input-error' : '' }}"
                       placeholder="https://github.com/usuario/repositorio">
                @error('github_url')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400 mt-1.5">{{ __('Se preenchido, o repositório será clonado no diretório raiz.') }}</p>
            </div>

            <div>
                <label class="label" for="notes"><i class="fas fa-sticky-note mr-1.5 text-gray-400"></i>{{ __('Observações') }}</label>
                <textarea name="notes" id="notes" rows="2" class="input">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check-circle"></i> {{ __('Criar Virtual Host') }}
                </button>
                <a href="{{ route('virtual-hosts.index') }}" class="btn-secondary">
                    <i class="fas fa-times"></i> {{ __('Cancelar') }}
                </a>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('.toggle').forEach(function(toggle) {
            toggle.addEventListener('click', function() {
                var checkbox = this.nextElementSibling;
                if (checkbox && checkbox.type === 'checkbox') {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        });

        function checkPort(port) {
            var status = document.getElementById('port-status');
            if (!port || port < 1 || port > 65535) {
                status.classList.add('hidden');
                return;
            }
            fetch('/api/port-check/' + port)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    status.classList.remove('hidden');
                    if (data.available) {
                        status.innerHTML = '<i class="fas fa-check-circle text-emerald-500"></i> {{ __("Disponível") }}';
                    } else {
                        status.innerHTML = '<i class="fas fa-exclamation-triangle text-amber-500"></i> {{ __("Em uso") }} por ' + (data.info ? data.info.process : '—');
                    }
                })
                .catch(function() {
                    status.classList.add('hidden');
                });
        }
    </script>
@endsection
