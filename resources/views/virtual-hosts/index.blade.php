@extends('layouts.app')

@section('title', 'Virtual Hosts')

@section('content')
    <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
        <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-globe mr-2 text-indigo-600"></i>Virtual Hosts</h1>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('virtual-hosts.sync') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm inline-flex items-center"
               onclick="return confirm('Importar todos os hosts do Apache para o banco?')">
                <i class="fas fa-sync-alt mr-1"></i> Sincronizar do Apache
            </a>
            <a href="{{ route('virtual-hosts.export') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm inline-flex items-center">
                <i class="fas fa-download mr-1"></i> Exportar
            </a>
            <form action="{{ route('virtual-hosts.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm inline-flex items-center cursor-pointer">
                    <i class="fas fa-upload mr-1"></i> Importar
                    <input type="file" name="backup_file" accept=".json" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
            <a href="{{ route('virtual-hosts.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm inline-flex items-center">
                <i class="fas fa-plus-circle mr-1"></i> Novo Virtual Host
            </a>
            <form action="{{ route('virtual-hosts.restart') }}" method="POST" class="restart-form"
                  onsubmit="return confirm('Reiniciar o Apache agora? A operacao pode levar alguns segundos.')">
                @csrf
                <button type="submit"
                   class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-sm inline-flex items-center">
                    <i class="fas fa-redo-alt mr-1"></i> Reiniciar Apache
                </button>
            </form>
        </div>
    </div>

    <div class="mb-6">
        <form method="GET" action="{{ route('virtual-hosts.index') }}" id="search-form">
            <label for="search-input"
                   class="flex items-center gap-3 w-full border-2 border-gray-200 dark:border-gray-600 rounded-full px-4 py-3 bg-gray-50 dark:bg-gray-700 focus-within:border-indigo-400 dark:focus-within:border-indigo-500 focus-within:bg-white dark:focus-within:bg-gray-700 focus-within:ring-2 focus-within:ring-indigo-100 dark:focus-within:ring-indigo-900 transition-all">
                <i class="fas fa-search text-gray-400 dark:text-gray-500 flex-shrink-0"></i>
                <input type="text" name="search" id="search-input" value="{{ $search ?? '' }}"
                       placeholder="Buscar por nome, diretório ou observações..."
                       class="flex-1 bg-transparent outline-none border-none text-base text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 min-w-0">
                @if ($search)
                    <a href="{{ route('virtual-hosts.index') }}" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors flex-shrink-0" title="Limpar busca">
                        <i class="fas fa-times-circle"></i>
                    </a>
                @endif
            </label>
        </form>
    </div>
    <script>
        (function () {
            var input = document.getElementById('search-input');
            var form = document.getElementById('search-form');
            var timer;
            if (input && form) {
                input.addEventListener('input', function () {
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        if (input.value.length >= 2 || input.value === '') {
                            form.submit();
                        }
                    }, 400);
                });
            }
        })();
    </script>

    @if ($search)
        <div class="mb-3 text-sm text-gray-500">
            <i class="fas fa-filter mr-1"></i> Resultados para "<strong>{{ $search }}</strong>" — {{ $vhosts->total() }} encontrado{{ $vhosts->total() !== 1 ? 's' : '' }}
        </div>
    @endif

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <th class="text-left px-4 py-3"><i class="fas fa-server mr-1"></i>Servidor</th>
                    <th class="text-left px-4 py-3"><i class="fas fa-folder mr-1"></i>Diretório Raiz</th>
                    <th class="text-center px-4 py-3"><i class="fas fa-lock mr-1"></i>SSL</th>
                    <th class="text-center px-4 py-3"><i class="fas fa-certificate mr-1"></i>Cert</th>
                    <th class="text-center px-4 py-3"><i class="fas fa-plug mr-1"></i>Porta</th>
                    <th class="text-center px-4 py-3"><i class="fas fa-power-off mr-1"></i>Ativo</th>
                    <th class="text-center px-4 py-3"><i class="fas fa-check-circle mr-1"></i>No Apache</th>
                    <th class="text-center px-4 py-3"><i class="fas fa-tools mr-1"></i>Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($vhosts as $vhost)
                    <tr class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 {{ !$vhost->active ? 'opacity-60' : '' }}">
                        <td class="px-4 py-3 font-medium">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $vhost->active ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                <a href="{{ $vhost->ssl_enabled ? 'https' : 'http' }}://{{ $vhost->server_name }}"
                                   target="_blank" rel="noopener noreferrer"
                                   class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                    {{ $vhost->server_name }}
                                </a>
                                <button type="button" onclick="copyToClipboard('{{ $vhost->server_name }}')" class="text-gray-400 hover:text-gray-600" title="Copiar server_name">
                                    <i class="fas fa-copy text-xs"></i>
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            <div class="flex items-center gap-1">
                                <span>{{ $vhost->document_root }}</span>
                                <button type="button" onclick="copyToClipboard('{{ addslashes($vhost->document_root) }}')" class="text-gray-400 hover:text-gray-600" title="Copiar document_root">
                                    <i class="fas fa-copy text-xs"></i>
                                </button>
                                <button type="button" onclick="openInVSCode('{{ $vhost->document_root }}')" class="text-blue-400 hover:text-blue-600" title="Abrir no VS Code">
                                    <i class="fas fa-code text-xs"></i>
                                </button>
                            </div>
                            @if ($vhost->template)
                                <span class="text-gray-400 text-xs mt-1 inline-block bg-gray-100 px-1.5 py-0.5 rounded">{{ $vhost->template }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($vhost->ssl_enabled)
                                <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-medium px-2 py-0.5 rounded-full"><i class="fas fa-lock"></i> HTTPS</span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-xs font-medium px-2 py-0.5 rounded-full"><i class="fas fa-globe"></i> HTTP</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-lg">
                            @if ($vhost->ssl_enabled)
                                @if ($certStatus[$vhost->server_name] ?? false)
                                    <span class="text-green-500" title="Certificado existe">✅</span>
                                @else
                                    <span class="text-red-500" title="Certificado nao encontrado">❌</span>
                                @endif
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">{{ $vhost->port }}</td>
                        <td class="px-4 py-3 text-center">
                            <form action="{{ route('virtual-hosts.toggle', $vhost) }}" method="POST" class="inline"
                                  onsubmit="return confirm('{{ $vhost->active ? 'Desativar' : 'Ativar' }} {{ $vhost->server_name }}?')">
                                @csrf
                                <button type="submit" class="text-xs font-medium inline-flex items-center gap-1 {{ $vhost->active ? 'text-green-600' : 'text-gray-400' }} hover:underline">
                                    <i class="fas {{ $vhost->active ? 'fa-toggle-on' : 'fa-toggle-off' }} text-lg"></i>
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if (in_array($vhost->server_name, $apacheNames ?? []))
                                <span class="text-green-600"><i class="fas fa-check-circle"></i> Sim</span>
                            @else
                                <span class="text-red-500"><i class="fas fa-times-circle"></i> Não</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('virtual-hosts.show', $vhost) }}" title="Ver detalhes"
                               class="text-gray-600 hover:text-gray-900 text-xs font-medium mr-1"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('virtual-hosts.edit', $vhost) }}" title="Editar"
                               class="text-indigo-600 hover:text-indigo-900 text-xs font-medium mr-1"><i class="fas fa-edit"></i></a>
                            @if ($vhost->ssl_enabled)
                            <form action="{{ route('virtual-hosts.regenerate-cert', $vhost) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Regenerar certificado SSL de {{ $vhost->server_name }}?')">
                                @csrf
                                <button type="submit" class="text-orange-500 hover:text-orange-700 text-xs font-medium mr-1" title="Regenerar certificado"><i class="fas fa-certificate"></i></button>
                            </form>
                            @endif
                            <form action="{{ route('virtual-hosts.destroy', $vhost) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Excluir {{ $vhost->server_name }}? Isso remove o hosts, certificado SSL e config do Apache.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-medium" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                            <i class="fas fa-globe text-4xl text-gray-300 mb-3 block"></i>
                            <p class="mb-2">Nenhum virtual host cadastrado ainda.</p>
                            <a href="{{ route('virtual-hosts.create') }}" class="text-indigo-600 hover:underline font-medium"><i class="fas fa-plus-circle"></i> Criar o primeiro</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $vhosts->links() }}
    </div>
@endsection
