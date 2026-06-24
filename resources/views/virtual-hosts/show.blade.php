@extends('layouts.app')

@section('title', $virtualHost->server_name)

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $virtualHost->server_name }}</h1>
            <p class="text-gray-500 text-sm mt-1">Detalhes do virtual host</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('virtual-hosts.edit', $virtualHost) }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                Editar
            </a>
            <a href="{{ route('virtual-hosts.index') }}"
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm">
                Voltar
            </a>
        </div>
    </div>

    <div class="bg-white rounded shadow overflow-hidden max-w-2xl">
        <table class="w-full text-sm">
            <tbody class="divide-y">
                <tr>
                    <th class="bg-gray-50 text-left px-4 py-3 font-medium text-gray-600 w-1/3">Servidor</th>
                    <td class="px-4 py-3">
                        <a href="{{ $virtualHost->ssl_enabled ? 'https' : 'http' }}://{{ $virtualHost->server_name }}"
                           target="_blank" rel="noopener noreferrer"
                           class="text-indigo-600 hover:text-indigo-900 hover:underline">
                            {{ $virtualHost->server_name }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-left px-4 py-3 font-medium text-gray-600">Diretório Raiz</th>
                    <td class="px-4 py-3 text-gray-600">{{ $virtualHost->document_root }}</td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-left px-4 py-3 font-medium text-gray-600">Porta</th>
                    <td class="px-4 py-3">{{ $virtualHost->port }}</td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-left px-4 py-3 font-medium text-gray-600">SSL</th>
                    <td class="px-4 py-3">
                        @if ($virtualHost->ssl_enabled)
                            <span class="text-green-600 font-bold">Ativado</span>
                        @else
                            <span class="text-gray-400">Desativado</span>
                        @endif
                    </td>
                </tr>
                @if ($virtualHost->github_url)
                <tr>
                    <th class="bg-gray-50 text-left px-4 py-3 font-medium text-gray-600">GitHub</th>
                    <td class="px-4 py-3">
                        <a href="{{ $virtualHost->github_url }}" target="_blank" rel="noopener noreferrer"
                           class="text-indigo-600 hover:text-indigo-900 hover:underline flex items-center gap-1">
                            <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
                            {{ $virtualHost->github_url }}
                        </a>
                    </td>
                </tr>
                @endif
                @if ($virtualHost->notes)
                <tr>
                    <th class="bg-gray-50 text-left px-4 py-3 font-medium text-gray-600">Observações</th>
                    <td class="px-4 py-3 text-gray-600 whitespace-pre-wrap">{{ $virtualHost->notes }}</td>
                </tr>
                @endif
                <tr>
                    <th class="bg-gray-50 text-left px-4 py-3 font-medium text-gray-600">Criado em</th>
                    <td class="px-4 py-3 text-gray-600">{{ $virtualHost->created_at->format('d/m/Y H:i:s') }}</td>
                </tr>
                <tr>
                    <th class="bg-gray-50 text-left px-4 py-3 font-medium text-gray-600">Atualizado em</th>
                    <td class="px-4 py-3 text-gray-600">{{ $virtualHost->updated_at->format('d/m/Y H:i:s') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex gap-2">
        <form action="{{ route('virtual-hosts.regenerate-cert', $virtualHost) }}" method="POST" class="inline"
              onsubmit="return confirm('Regenerar certificado SSL para {{ $virtualHost->server_name }}?')">
            @csrf
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-sm">
                Regenerar Certificado
            </button>
        </form>
        <form action="{{ route('virtual-hosts.destroy', $virtualHost) }}" method="POST" class="inline"
              onsubmit="return confirm('Excluir {{ $virtualHost->server_name }}? Isso vai remover o hosts, o certificado SSL e a config do Apache.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">
                Excluir
            </button>
        </form>
    </div>
@endsection
