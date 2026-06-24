@extends('layouts.app')

@section('title', 'Editar Virtual Host')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Editar: {{ $virtualHost->server_name }}</h1>
    </div>

    <div class="bg-white rounded shadow p-6 max-w-2xl">
        <form action="{{ route('virtual-hosts.update', $virtualHost) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Servidor</label>
                <input type="text" name="server_name" value="{{ old('server_name', $virtualHost->server_name) }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('server_name') border-red-500 @enderror"
                       placeholder="meusite.local">
                @error('server_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Diretório Raiz</label>
                <input type="text" name="document_root" value="{{ old('document_root', $virtualHost->document_root) }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('document_root') border-red-500 @enderror">
                @error('document_root')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Porta</label>
                <input type="number" name="port" value="{{ old('port', $virtualHost->port) }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('port') border-red-500 @enderror">
                @error('port')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="ssl_enabled" value="1"
                           {{ old('ssl_enabled', $virtualHost->ssl_enabled) ? 'checked' : '' }}
                           class="rounded border-gray-300">
                    <span class="text-sm font-medium text-gray-700">Habilitar SSL (HTTPS)</span>
                </label>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">GitHub</label>
                <input type="url" name="github_url" value="{{ old('github_url', $virtualHost->github_url) }}"
                       class="w-full border rounded px-3 py-2 text-sm @error('github_url') border-red-500 @enderror"
                       placeholder="https://github.com/usuario/repositorio">
                @error('github_url')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2 text-sm">{{ old('notes', $virtualHost->notes) }}</textarea>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded text-sm font-medium">
                    Salvar Alterações
                </button>
                <a href="{{ route('virtual-hosts.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection
