@extends('layouts.app')

@section('title', 'Novo Virtual Host')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight"><i class="fas fa-plus-circle text-blue-500 mr-2"></i>Novo Virtual Host</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Cria o hosts, configuração do Apache e certificado SSL automaticamente.</p>
    </div>

    <div class="card p-6 max-w-2xl">
        <form action="{{ route('virtual-hosts.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="label" for="server_name"><i class="fas fa-server mr-1.5 text-gray-400"></i>Nome do Servidor</label>
                <input type="text" name="server_name" id="server_name" value="{{ old('server_name') }}"
                       class="input {{ $errors->has('server_name') ? 'input-error' : '' }}"
                       placeholder="meusite.local">
                @error('server_name')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400 mt-1.5">Ex: meusite.local, projeto.test</p>
            </div>

            <div>
                <label class="label" for="document_root"><i class="fas fa-folder mr-1.5 text-gray-400"></i>Diretório Raiz</label>
                <div class="flex gap-2">
                    <input type="text" name="document_root" id="document_root" value="{{ old('document_root', config('virtualhosts.default_document_root')) }}"
                           class="input flex-1 {{ $errors->has('document_root') ? 'input-error' : '' }}"
                           placeholder="D:/www/meusite/public">
                    <button type="button" onclick="openExplorer()" class="btn-secondary" title="Abrir no Explorer">
                        <i class="fas fa-folder-open"></i>
                    </button>
                </div>
                @error('document_root')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label" for="port"><i class="fas fa-plug mr-1.5 text-gray-400"></i>Porta</label>
                <input type="number" name="port" id="port" value="{{ old('port', 80) }}"
                       class="input {{ $errors->has('port') ? 'input-error' : '' }}">
                @error('port')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label" for="template"><i class="fas fa-magic mr-1.5 text-gray-400"></i>Template do Projeto</label>
                <select name="template" id="template" class="input">
                    <option value="" {{ !old('template') ? 'selected' : '' }}>Nenhum (apenas criar vhost)</option>
                    <option value="laravel" {{ old('template') === 'laravel' ? 'selected' : '' }}>Laravel (composer create-project)</option>
                    <option value="wordpress" {{ old('template') === 'wordpress' ? 'selected' : '' }}>WordPress (download + extract)</option>
                    <option value="html" {{ old('template') === 'html' ? 'selected' : '' }}>HTML + Tailwind (estático)</option>
                    <option value="php" {{ old('template') === 'php' ? 'selected' : '' }}>PHP Puro (index.php + config.php)</option>
                </select>
                <p class="text-xs text-gray-400 mt-1.5">Escolha um template para iniciar o projeto automaticamente no diretório. Se preencher o GitHub, o template é ignorado.</p>
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
                <label for="ssl_enabled" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer select-none"><i class="fas fa-lock mr-1.5 text-gray-400"></i>Habilitar SSL (HTTPS)</label>
            </div>
            <p class="text-xs text-gray-400 mt-1.5 ml-11">Gera certificado via mkcert automaticamente.</p>

            <div>
                <label class="label" for="github_url"><i class="fab fa-github mr-1.5 text-gray-400"></i>GitHub (clonar repositório)</label>
                <input type="url" name="github_url" id="github_url" value="{{ old('github_url') }}"
                       class="input {{ $errors->has('github_url') ? 'input-error' : '' }}"
                       placeholder="https://github.com/usuario/repositorio">
                @error('github_url')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400 mt-1.5">Se preenchido, o repositório será clonado no diretório raiz.</p>
            </div>

            <div>
                <label class="label" for="notes"><i class="fas fa-sticky-note mr-1.5 text-gray-400"></i>Observações</label>
                <textarea name="notes" id="notes" rows="2" class="input">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check-circle"></i> Criar Virtual Host
                </button>
                <a href="{{ route('virtual-hosts.index') }}" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
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
    </script>
@endsection
