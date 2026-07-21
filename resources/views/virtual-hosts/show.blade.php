@extends('layouts.app')

@section('title', $virtualHost->server_name)

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full {{ $virtualHost->active ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">{{ $virtualHost->server_name }}</h1>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Detalhes do virtual host') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ $virtualHost->ssl_enabled ? 'https' : 'http' }}://{{ $virtualHost->server_name }}" target="_blank" rel="noopener noreferrer"
               class="btn-primary btn-sm" title="{{ __('Abrir no browser') }}">
                <i class="fas fa-external-link-alt"></i> {{ __('Abrir') }}
            </a>
            <a href="{{ route('virtual-hosts.edit', $virtualHost) }}" class="btn-secondary btn-sm">
                <i class="fas fa-edit"></i> {{ __('Editar') }}
            </a>
            <a href="{{ route('virtual-hosts.index') }}" class="btn-ghost btn-sm">
                <i class="fas fa-arrow-left"></i> {{ __('Voltar') }}
            </a>
        </div>
    </div>

    <div class="card overflow-hidden max-w-2xl">
        <table class="w-full text-sm">
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                <tr>
                    <th class="bg-gray-50/80 dark:bg-gray-800/80 text-left px-5 py-3.5 font-medium text-gray-600 dark:text-gray-400 w-1/3"><i class="fas fa-server mr-2 text-gray-400"></i>{{ __('Servidor') }}</th>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <a href="{{ $virtualHost->ssl_enabled ? 'https' : 'http' }}://{{ $virtualHost->server_name }}"
                               target="_blank" rel="noopener noreferrer"
                               class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                {{ $virtualHost->server_name }}
                            </a>
                            <button type="button" onclick="copyToClipboard('{{ $virtualHost->server_name }}')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" title="{{ __('Copiar server_name') }}">
                                <i class="fas fa-copy"></i>
                            </button>
                            <a href="{{ $virtualHost->ssl_enabled ? 'https' : 'http' }}://{{ $virtualHost->server_name }}"
                               target="_blank" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 text-xs" title="{{ __('Abrir site') }}">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <button type="button" onclick="testDns('{{ $virtualHost->server_name }}')"
                               class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors text-xs" title="{{ __('Testar DNS') }}">
                                <i class="fas fa-network-wired"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-50/80 dark:bg-gray-800/80 text-left px-5 py-3.5 font-medium text-gray-600 dark:text-gray-400"><i class="fas fa-folder mr-2 text-gray-400"></i>{{ __('Diretório Raiz') }}</th>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-700 dark:text-gray-300 font-mono text-xs">{{ $virtualHost->document_root }}</span>
                            <button type="button" onclick="copyToClipboard('{{ addslashes($virtualHost->document_root) }}')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" title="{{ __('Copiar document_root') }}">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <div class="flex gap-2 mt-2">
                            @if (\Illuminate\Support\Facades\File::exists($virtualHost->document_root))
                                <a href="file:///{{ str_replace('/', '\\', $virtualHost->document_root) }}" target="_blank"
                                   class="btn-ghost btn-xs text-blue-600 dark:text-blue-400" title="{{ __('Abrir no Explorer') }}">
                                    <i class="fas fa-folder-open"></i> {{ __('Explorer') }}
                                </a>
                                <button type="button" onclick="openInVSCode('{{ $virtualHost->document_root }}')"
                                   class="btn-ghost btn-xs text-blue-600 dark:text-blue-400" title="{{ __('Abrir no VS Code') }}">
                                    <i class="fas fa-code"></i> {{ __('VS Code') }}
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-50/80 dark:bg-gray-800/80 text-left px-5 py-3.5 font-medium text-gray-600 dark:text-gray-400"><i class="fas fa-plug mr-2 text-gray-400"></i>{{ __('Porta') }}</th>
                    <td class="px-5 py-3.5"><span class="font-mono text-sm text-gray-700 dark:text-gray-300">{{ $virtualHost->port }}</span></td>
                </tr>
                <tr>
                    <th class="bg-gray-50/80 dark:bg-gray-800/80 text-left px-5 py-3.5 font-medium text-gray-600 dark:text-gray-400"><i class="fas fa-power-off mr-2 text-gray-400"></i>{{ __('Status') }}</th>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            @if ($virtualHost->active)
                                <span class="badge-green"><i class="fas fa-check-circle"></i> {{ __('Ativo') }}</span>
                            @else
                                <span class="badge-gray"><i class="fas fa-times-circle"></i> {{ __('Inativo') }}</span>
                            @endif
                            <form action="{{ route('virtual-hosts.toggle', $virtualHost) }}" method="POST" class="inline"
                                  onsubmit="return confirm('{{ $virtualHost->active ? __('Desativar') : __('Ativar') }} {{ $virtualHost->server_name }}?')">
                                @csrf
                                <button type="submit" class="btn-ghost btn-xs text-blue-600 dark:text-blue-400">
                                    <i class="fas fa-toggle-{{ $virtualHost->active ? 'on' : 'off' }}"></i>
                                    {{ $virtualHost->active ? __('Desativar') : __('Ativar') }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-50/80 dark:bg-gray-800/80 text-left px-5 py-3.5 font-medium text-gray-600 dark:text-gray-400"><i class="fas fa-lock mr-2 text-gray-400"></i>{{ __('SSL') }}</th>
                    <td class="px-5 py-3.5">
                        @if ($virtualHost->ssl_enabled)
                            <div class="flex items-center gap-2">
                                <span class="badge-green"><i class="fas fa-lock"></i> {{ __('HTTPS') }}</span>
                                @php $certPath = config('virtualhosts.mkcert_dir') . '/' . $virtualHost->server_name . '.pem'; @endphp
                                @if (\Illuminate\Support\Facades\File::exists($certPath))
                                    <span class="text-emerald-600 dark:text-emerald-400 text-xs"><i class="fas fa-check-circle"></i> {{ __('Certificado presente') }}</span>
                                @else
                                    <span class="text-red-400 text-xs"><i class="fas fa-exclamation-circle"></i> {{ __('Certificado não encontrado') }}</span>
                                @endif
                            </div>
                        @else
                            <span class="badge-gray"><i class="fas fa-globe"></i> {{ __('HTTP') }}</span>
                        @endif
                    </td>
                </tr>
                @if ($virtualHost->template)
                <tr>
                    <th class="bg-gray-50/80 dark:bg-gray-800/80 text-left px-5 py-3.5 font-medium text-gray-600 dark:text-gray-400"><i class="fas fa-magic mr-2 text-gray-400"></i>{{ __('Template') }}</th>
                    <td class="px-5 py-3.5"><span class="badge-gray">{{ ucfirst($virtualHost->template) }}</span></td>
                </tr>
                @endif
                @if ($virtualHost->php_version)
                <tr>
                    <th class="bg-gray-50/80 dark:bg-gray-800/80 text-left px-5 py-3.5 font-medium text-gray-600 dark:text-gray-400"><i class="fab fa-php mr-2 text-gray-400"></i>{{ __('PHP') }}</th>
                    <td class="px-5 py-3.5"><span class="badge-blue">PHP {{ $virtualHost->php_version }}</span></td>
                </tr>
                @endif
                @if ($virtualHost->github_url)
                <tr>
                    <th class="bg-gray-50/80 dark:bg-gray-800/80 text-left px-5 py-3.5 font-medium text-gray-600 dark:text-gray-400"><i class="fab fa-github mr-2 text-gray-400"></i>{{ __('GitHub') }}</th>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <a href="{{ $virtualHost->github_url }}" target="_blank" rel="noopener noreferrer"
                               class="text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1.5 text-sm">
                                <i class="fab fa-github"></i>
                                {{ $virtualHost->github_url }}
                            </a>
                            <a href="{{ $virtualHost->github_url }}" target="_blank"
                               class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xs" title="{{ __('Abrir repositório') }}">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endif
                @if ($virtualHost->notes)
                <tr>
                    <th class="bg-gray-50/80 dark:bg-gray-800/80 text-left px-5 py-3.5 font-medium text-gray-600 dark:text-gray-400"><i class="fas fa-sticky-note mr-2 text-gray-400"></i>{{ __('Observações') }}</th>
                    <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300 whitespace-pre-wrap text-sm">{{ $virtualHost->notes }}</td>
                </tr>
                @endif
                <tr>
                    <th class="bg-gray-50/80 dark:bg-gray-800/80 text-left px-5 py-3.5 font-medium text-gray-600 dark:text-gray-400"><i class="fas fa-calendar-plus mr-2 text-gray-400"></i>{{ __('Criado em') }}</th>
                    <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300 text-sm">{{ $virtualHost->created_at->format('d/m/Y H:i:s') }}</td>
                </tr>
                <tr>
                    <th class="bg-gray-50/80 dark:bg-gray-800/80 text-left px-5 py-3.5 font-medium text-gray-600 dark:text-gray-400"><i class="fas fa-calendar-check mr-2 text-gray-400"></i>{{ __('Atualizado em') }}</th>
                    <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300 text-sm">{{ $virtualHost->updated_at->format('d/m/Y H:i:s') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-5 flex gap-2 flex-wrap">
        <a href="{{ route('virtual-hosts.clone', $virtualHost) }}" class="btn-secondary btn-sm">
            <i class="fas fa-clone"></i> {{ __('Clonar') }}
        </a>
        @if ($virtualHost->ssl_enabled)
        <form action="{{ route('virtual-hosts.regenerate-cert', $virtualHost) }}" method="POST" class="inline"
              onsubmit="return confirm('{{ __('Regenerar certificado SSL para') }} {{ $virtualHost->server_name }}?')">
            @csrf
            <button type="submit" class="btn-warning btn-sm">
                <i class="fas fa-certificate"></i> {{ __('Regenerar Certificado') }}
            </button>
        </form>
        @endif
        <form action="{{ route('virtual-hosts.toggle', $virtualHost) }}" method="POST" class="inline"
              onsubmit="return confirm('{{ $virtualHost->active ? __('Desativar') : __('Ativar') }} {{ $virtualHost->server_name }}?')">
            @csrf
            <button type="submit" class="btn-secondary btn-sm">
                <i class="fas fa-toggle-{{ $virtualHost->active ? 'on' : 'off' }}"></i>
                {{ $virtualHost->active ? __('Desativar') : __('Ativar') }}
            </button>
        </form>
        <form action="{{ route('virtual-hosts.destroy', $virtualHost) }}" method="POST" class="inline"
              onsubmit="return confirm('{{ __('Excluir') }} {{ $virtualHost->server_name }}? {{ __('Isso vai remover o hosts, o certificado SSL e a config do Apache.') }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger btn-sm">
                <i class="fas fa-trash-alt"></i> {{ __('Excluir') }}
            </button>
        </form>
    </div>

    <div id="dns-result" class="hidden mt-4 card p-4 max-w-2xl">
        <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-network-wired text-gray-400"></i>
            <span class="font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('Teste de DNS') }}</span>
        </div>
        <div id="dns-content" class="text-sm text-gray-600 dark:text-gray-400 font-mono whitespace-pre-wrap"></div>
    </div>

    <script>
        function testDns(hostname) {
            var result = document.getElementById('dns-result');
            var content = document.getElementById('dns-content');
            result.classList.remove('hidden');
            content.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> {{ __("Testando...") }}';

            fetch('/api/dns-test/' + encodeURIComponent(hostname))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var html = '';
                    if (data.resolved) {
                        html += '<span class="text-emerald-500"><i class="fas fa-check-circle"></i> {{ __("Resolvido") }}</span>: ' + data.ip + '\n';
                    } else {
                        html += '<span class="text-red-500"><i class="fas fa-times-circle"></i> {{ __("Não resolvido") }}</span>\n';
                    }
                    if (data.ping_ms !== null) {
                        html += '{{ __("Ping") }}: ' + data.ping_ms + 'ms (TTL)\n';
                    }
                    html += '\n' + data.raw;
                    content.textContent = '';
                    content.innerHTML = html.replace(/\n/g, '<br>');
                })
                .catch(function() {
                    content.innerHTML = '<span class="text-red-500">{{ __("Erro ao testar DNS") }}</span>';
                });
        }
    </script>
@endsection
