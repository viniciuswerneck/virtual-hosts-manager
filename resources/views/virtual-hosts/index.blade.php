@extends('layouts.app')

@section('title', 'Virtual Hosts')

@section('content')
    <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">Virtual Hosts</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerencie seus domínios locais</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('virtual-hosts.sync') }}"
               class="btn btn-secondary btn-sm"
               onclick="return confirm('Importar todos os hosts do Apache para o banco?')">
                <i class="fas fa-sync-alt"></i> Sincronizar
            </a>
            <a href="{{ route('virtual-hosts.export') }}"
               class="btn btn-secondary btn-sm">
                <i class="fas fa-download"></i> Exportar
            </a>
            <form action="{{ route('virtual-hosts.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="btn btn-secondary btn-sm cursor-pointer">
                    <i class="fas fa-upload"></i> Importar
                    <input type="file" name="backup_file" accept=".json" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
            <a href="{{ route('backup.export') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-archive"></i> Backup
            </a>
            <form action="{{ route('backup.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label class="btn btn-secondary btn-sm cursor-pointer">
                    <i class="fas fa-file-import"></i> Restore
                    <input type="file" name="backup_file" accept=".zip" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
            <a href="{{ route('virtual-hosts.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle"></i> Novo Virtual Host
            </a>
            <form action="{{ route('virtual-hosts.restart') }}" method="POST" class="restart-form"
                  onsubmit="return confirm('Reiniciar o Apache agora? A operacao pode levar alguns segundos.')">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="fas fa-redo-alt"></i> Reiniciar Apache
                </button>
            </form>
        </div>
    </div>

    <div class="mb-6">
        <form method="GET" action="{{ route('virtual-hosts.index') }}" id="search-form">
            <label for="search-input"
                   class="flex items-center gap-3 w-full rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm shadow-sm focus-within:border-blue-500 dark:focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-500/20 dark:focus-within:ring-blue-400/20 transition-all">
                <i class="fas fa-search text-gray-400 dark:text-gray-500 shrink-0"></i>
                <input type="text" name="search" id="search-input" value="{{ $search ?? '' }}"
                       placeholder="Buscar por nome, diretório ou observações..."
                       class="flex-1 bg-transparent outline-none border-none text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 min-w-0">
                @if ($search)
                    <a href="{{ route('virtual-hosts.index') }}" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors shrink-0" title="Limpar busca">
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
        <div class="mb-3 text-sm text-gray-500 dark:text-gray-400">
            <i class="fas fa-filter mr-1"></i> Resultados para "<strong class="text-gray-700 dark:text-gray-200">{{ $search }}</strong>" — {{ $vhosts->total() }} encontrado{{ $vhosts->total() !== 1 ? 's' : '' }}
        </div>
    @endif

    <div id="batch-bar" class="hidden mb-4 flex items-center gap-3 p-3 rounded-xl bg-blue-50/80 border border-blue-200/60 dark:bg-blue-950/20 dark:border-blue-800/30">
        <span class="text-sm text-blue-700 dark:text-blue-300" id="batch-count">0</span>
        <span class="text-sm text-blue-700 dark:text-blue-300">selecionado(s)</span>
        <div class="flex gap-2 ml-auto">
            <button type="button" onclick="batchAction('activate')" class="btn btn-primary btn-xs">
                <i class="fas fa-check-circle"></i> Ativar
            </button>
            <button type="button" onclick="batchAction('deactivate')" class="btn btn-secondary btn-xs">
                <i class="fas fa-pause-circle"></i> Desativar
            </button>
            <button type="button" onclick="batchAction('delete')" class="btn btn-danger btn-xs">
                <i class="fas fa-trash-alt"></i> Excluir
            </button>
        </div>
    </div>

    <form id="batch-form" method="POST" style="display:none">
        @csrf
        <input type="hidden" name="action" id="batch-action-input">
        <div id="batch-ids-container"></div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-gray-800/80 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="text-left px-4 py-3.5 w-10">
                            <input type="checkbox" id="select-all" onchange="toggleAll(this)" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="text-left px-4 py-3.5"><i class="fas fa-server mr-1.5"></i>Servidor</th>
                        <th class="text-left px-4 py-3.5"><i class="fas fa-folder mr-1.5"></i>Diretório Raiz</th>
                        <th class="text-center px-4 py-3.5"><i class="fas fa-lock mr-1.5"></i>SSL</th>
                        <th class="text-center px-4 py-3.5"><i class="fas fa-certificate mr-1.5"></i>Cert</th>
                        <th class="text-center px-4 py-3.5"><i class="fas fa-plug mr-1.5"></i>Porta</th>
                        <th class="text-center px-4 py-3.5"><i class="fas fa-power-off mr-1.5"></i>Ativo</th>
                        <th class="text-center px-4 py-3.5"><i class="fas fa-check-circle mr-1.5"></i>Apache</th>
                        <th class="text-center px-4 py-3.5"><i class="fas fa-tools mr-1.5"></i>Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse ($vhosts as $vhost)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors {{ !$vhost->active ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3.5">
                                <input type="checkbox" class="batch-checkbox rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                                       value="{{ $vhost->id }}" onchange="updateBatchBar()">
                            </td>
                            <td class="px-4 py-3.5 font-medium">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2 h-2 rounded-full shrink-0 {{ $vhost->active ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                    <a href="{{ $vhost->ssl_enabled ? 'https' : 'http' }}://{{ $vhost->server_name }}"
                                       target="_blank" rel="noopener noreferrer"
                                       class="text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $vhost->server_name }}
                                    </a>
                                    <button type="button" onclick="copyToClipboard('{{ $vhost->server_name }}')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" title="Copiar server_name">
                                        <i class="fas fa-copy text-xs"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                                    <span class="truncate max-w-[180px]">{{ $vhost->document_root }}</span>
                                    <button type="button" onclick="copyToClipboard('{{ addslashes($vhost->document_root) }}')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" title="Copiar document_root">
                                        <i class="fas fa-copy text-xs"></i>
                                    </button>
                                    <button type="button" onclick="openInVSCode('{{ $vhost->document_root }}')" class="text-blue-400 hover:text-blue-600 transition-colors" title="Abrir no VS Code">
                                        <i class="fas fa-code text-xs"></i>
                                    </button>
                                </div>
                                @if ($vhost->template)
                                    <span class="badge-gray mt-1">{{ $vhost->template }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if ($vhost->ssl_enabled)
                                    <span class="badge-green"><i class="fas fa-lock"></i> HTTPS</span>
                                @else
                                    <span class="badge-gray"><i class="fas fa-globe"></i> HTTP</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if ($vhost->ssl_enabled)
                                    @if ($certStatus[$vhost->server_name] ?? false)
                                        <span class="text-emerald-500" title="Certificado existe"><i class="fas fa-check-circle"></i></span>
                                    @else
                                        <span class="text-red-400" title="Certificado não encontrado"><i class="fas fa-exclamation-circle"></i></span>
                                    @endif
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center font-mono text-sm text-gray-700 dark:text-gray-300">{{ $vhost->port }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <form action="{{ route('virtual-hosts.toggle', $vhost) }}" method="POST" class="inline"
                                      onsubmit="return confirm('{{ $vhost->active ? 'Desativar' : 'Ativar' }} {{ $vhost->server_name }}?')">
                                    @csrf
                                    <button type="submit" class="toggle {{ $vhost->active ? 'toggle-on' : 'toggle-off' }}" title="{{ $vhost->active ? 'Desativar' : 'Ativar' }}">
                                        <span class="toggle-dot"></span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if (in_array($vhost->server_name, $apacheNames ?? []))
                                    <span class="text-emerald-600 dark:text-emerald-400 text-xs font-medium"><i class="fas fa-check-circle"></i> Sim</span>
                                @else
                                    <span class="text-red-400 dark:text-red-400 text-xs font-medium"><i class="fas fa-times-circle"></i> Não</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('virtual-hosts.show', $vhost) }}" title="Ver detalhes" class="btn-ghost btn-xs p-1.5">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('virtual-hosts.edit', $vhost) }}" title="Editar" class="btn-ghost btn-xs p-1.5 text-blue-600 dark:text-blue-400">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if ($vhost->ssl_enabled)
                                    <form action="{{ route('virtual-hosts.regenerate-cert', $vhost) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Regenerar certificado SSL de {{ $vhost->server_name }}?')">
                                        @csrf
                                        <button type="submit" class="btn-ghost btn-xs p-1.5 text-amber-600 dark:text-amber-400" title="Regenerar certificado">
                                            <i class="fas fa-certificate"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('virtual-hosts.destroy', $vhost) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Excluir {{ $vhost->server_name }}? Isso remove o hosts, certificado SSL e config do Apache.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost btn-xs p-1.5 text-red-600 dark:text-red-400" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 text-gray-400 mb-3 dark:bg-gray-700">
                                    <i class="fas fa-globe text-xl"></i>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Nenhum virtual host cadastrado ainda.</p>
            <a href="{{ route('virtual-hosts.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus-circle"></i> Criar o primeiro
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $vhosts->links() }}
    </div>

    <script>
        function toggleAll(master) {
            document.querySelectorAll('.batch-checkbox').forEach(function(cb) {
                cb.checked = master.checked;
            });
            updateBatchBar();
        }
        function updateBatchBar() {
            var checked = document.querySelectorAll('.batch-checkbox:checked');
            var bar = document.getElementById('batch-bar');
            if (checked.length === 0) {
                bar.classList.add('hidden');
                return;
            }
            bar.classList.remove('hidden');
            document.getElementById('batch-count').textContent = checked.length;
        }
        function batchAction(action) {
            if (!confirm('Tem certeza que deseja ' + (action === 'activate' ? 'ativar' : action === 'deactivate' ? 'desativar' : 'excluir') + ' os vhosts selecionados?')) return;
            var checked = document.querySelectorAll('.batch-checkbox:checked');
            var container = document.getElementById('batch-ids-container');
            container.innerHTML = '';
            checked.forEach(function(cb) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });
            var form = document.getElementById('batch-form');
            document.getElementById('batch-action-input').value = action;
            if (action === 'delete') {
                form.action = '{{ route('virtual-hosts.batch.delete') }}';
            } else {
                form.action = '{{ route('virtual-hosts.batch.toggle') }}';
            }
            form.submit();
        }
    </script>
@endsection
