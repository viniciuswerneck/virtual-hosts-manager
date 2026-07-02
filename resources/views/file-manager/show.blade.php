@extends('layouts.app')

@section('title', $name)

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">
                <i class="fas fa-file-code text-blue-500 mr-2"></i>{{ $name }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-mono">{{ $filePath }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('file-manager.index', ['path' => $dir]) }}" class="btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <button onclick="openInVSCode('{{ $dir }}')" class="btn-secondary btn-sm">
                <i class="fas fa-code"></i> Abrir no VS Code
            </button>
            <button onclick="openExplorerPath('{{ str_replace('/', '\\', $dir) }}')" class="btn-secondary btn-sm">
                <i class="fas fa-folder-open"></i> Explorer
            </button>
        </div>
    </div>

    <div class="rounded-xl overflow-hidden border border-gray-700/50 shadow-lg">
        <div class="px-4 py-2.5 bg-gray-800 dark:bg-gray-900 text-gray-400 text-xs flex items-center justify-between">
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <i class="fas fa-code mr-1"></i> {{ $name }}
            </span>
            <button onclick="copyToClipboard(document.getElementById('file-content').textContent)"
                    class="hover:text-white transition-colors flex items-center gap-1.5">
                <i class="fas fa-copy"></i> Copiar
            </button>
        </div>
        <pre id="file-content" class="p-4 text-sm font-mono text-gray-100 bg-gray-950 overflow-x-auto max-h-[600px] overflow-y-auto leading-relaxed" style="white-space: pre-wrap; word-break: break-all;">{{ $content }}</pre>
    </div>

    <script>
        function openExplorerPath(path) {
            window.open('file:///' + path, '_blank');
        }
        function openInVSCode(path) {
            var vsPath = path.replace(/\\/g, '/');
            window.open('vscode://file/' + vsPath, '_blank');
        }
    </script>
@endsection
