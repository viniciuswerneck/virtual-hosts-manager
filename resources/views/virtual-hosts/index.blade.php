@extends('layouts.app')

@section('title', 'Virtual Hosts')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-globe mr-2 text-indigo-600"></i>Virtual Hosts</h1>
        <div class="flex gap-2">
            <a href="{{ route('virtual-hosts.sync') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm"
               onclick="return confirm('Importar todos os hosts do Apache para o banco?')">
                <i class="fas fa-sync-alt mr-1"></i> Sincronizar do Apache
            </a>
            <a href="{{ route('virtual-hosts.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                <i class="fas fa-plus-circle mr-1"></i> Novo Virtual Host
            </a>
            <form action="{{ route('virtual-hosts.restart') }}" method="POST" class="inline"
                  onsubmit="return confirm('Reiniciar o Apache?')">
                @csrf
                <button type="submit"
                   class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-sm">
                    <i class="fas fa-redo-alt mr-1"></i> Reiniciar Apache
                </button>
            </form>
        </div>
    </div>

    <div class="mb-4">
        <form method="GET" action="{{ route('virtual-hosts.index') }}" class="flex gap-2">
            <div class="relative flex-1 max-w-md">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por nome, diretório ou observações..."
                       class="w-full border rounded pl-8 pr-3 py-2 text-sm bg-white">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                <i class="fas fa-search mr-1"></i> Buscar
            </button>
            @if ($search)
                <a href="{{ route('virtual-hosts.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm">
                    <i class="fas fa-times mr-1"></i> Limpar
                </a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <th class="text-left px-4 py-3"><i class="fas fa-server mr-1"></i>Servidor</th>
                    <th class="text-left px-4 py-3"><i class="fas fa-folder mr-1"></i>Diretório Raiz</th>
                    <th class="text-center px-4 py-3"><i class="fas fa-lock mr-1"></i>SSL</th>
                    <th class="text-center px-4 py-3"><i class="fas fa-plug mr-1"></i>Porta</th>
                    <th class="text-center px-4 py-3"><i class="fas fa-check-circle mr-1"></i>No Apache</th>
                    <th class="text-left px-4 py-3"><i class="fab fa-github mr-1"></i>GitHub</th>
                    <th class="text-right px-4 py-3"><i class="fas fa-tools mr-1"></i>Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($vhosts as $vhost)
                    <tr class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
                        <td class="px-4 py-3 font-medium">
                            <a href="{{ $vhost->ssl_enabled ? 'https' : 'http' }}://{{ $vhost->server_name }}"
                               target="_blank" rel="noopener noreferrer"
                               class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                {{ $vhost->server_name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $vhost->document_root }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($vhost->ssl_enabled)
                                <span class="text-green-600 font-bold"><i class="fas fa-check-circle"></i> Sim</span>
                            @else
                                <span class="text-gray-400"><i class="fas fa-times-circle"></i> Não</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">{{ $vhost->port }}</td>
                        <td class="px-4 py-3 text-center">
                            @if (in_array($vhost->server_name, $apacheNames ?? []))
                                <span class="text-green-600"><i class="fas fa-check-circle"></i> Sim</span>
                            @else
                                <span class="text-red-500"><i class="fas fa-times-circle"></i> Não</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($vhost->github_url)
                                <a href="{{ $vhost->github_url }}" target="_blank" rel="noopener noreferrer"
                                   class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                                    <svg class="w-5 h-5 inline" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                                </a>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('virtual-hosts.show', $vhost) }}"
                               class="text-gray-600 hover:text-gray-900 text-xs font-medium mr-2"><i class="fas fa-eye"></i> Ver</a>
                            <a href="{{ route('virtual-hosts.edit', $vhost) }}"
                               class="text-indigo-600 hover:text-indigo-900 text-xs font-medium mr-2"><i class="fas fa-edit"></i> Editar</a>
                            <form action="{{ route('virtual-hosts.regenerate-cert', $vhost) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Regenerar certificado SSL para {{ $vhost->server_name }}?')">
                                @csrf
                                <button type="submit" class="text-orange-500 hover:text-orange-700 text-xs font-medium mr-2"><i class="fas fa-certificate"></i> Cert</button>
                            </form>
                            <form action="{{ route('virtual-hosts.destroy', $vhost) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Excluir {{ $vhost->server_name }}? Isso vai remover o hosts, o certificado SSL e a config do Apache.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-medium"><i class="fas fa-trash-alt"></i> Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            Nenhum virtual host cadastrado ainda.
                            <a href="{{ route('virtual-hosts.create') }}" class="text-indigo-600 hover:underline"><i class="fas fa-plus-circle"></i> Criar o primeiro</a>
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
