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
            <form action="{{ route('virtual-hosts.restart') }}" method="POST" class="inline restart-form"
                  onsubmit="return confirm('Reiniciar o Apache agora? A operacao pode levar alguns segundos.')">
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
                    <th class="text-center px-4 py-3"><i class="fas fa-certificate mr-1"></i>Cert</th>
                    <th class="text-center px-4 py-3"><i class="fas fa-plug mr-1"></i>Porta</th>
                    <th class="text-center px-4 py-3"><i class="fas fa-check-circle mr-1"></i>No Apache</th>
                    <th class="text-center px-4 py-3"><i class="fas fa-tools mr-1"></i>Ações</th>
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
                            <form action="{{ route('virtual-hosts.regenerate-cert', $vhost) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Regenerar certificado SSL de {{ $vhost->server_name }}?')">
                                @csrf
                                <button type="submit" class="text-orange-500 hover:text-orange-700 text-xs font-medium mr-1" title="Regenerar certificado"><i class="fas fa-certificate"></i></button>
                            </form>
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
