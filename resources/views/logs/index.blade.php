@extends('layouts.app')

@section('title', 'Logs do Apache')

@section('content')
    <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-file-alt mr-2 text-indigo-600"></i>Logs do Apache</h1>
            @if ($logPath)
                <p class="text-gray-500 text-sm mt-1">{{ $logPath }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm">
                <i class="fas fa-arrow-left mr-1"></i> Voltar
            </a>
        </div>
    </div>

    @if (!$logPath || !file_exists($logPath))
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded text-sm mb-6">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Caminho do log não configurado ou arquivo não encontrado.
            <a href="{{ route('settings.index') }}" class="underline font-medium ml-1">Configurar em Settings</a>
        </div>
    @else
        <div class="bg-white rounded shadow p-4 mb-6">
            <form method="GET" action="{{ route('logs.index') }}" class="flex items-center gap-4 flex-wrap">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Nível:</label>
                    <select name="level" class="border rounded px-3 py-1.5 text-sm bg-white" onchange="this.form.submit()">
                        @foreach ($levels as $l)
                            <option value="{{ $l }}" {{ $level === $l ? 'selected' : '' }}>{{ ucfirst($l) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Linhas:</label>
                    <select name="lines" class="border rounded px-3 py-1.5 text-sm bg-white" onchange="this.form.submit()">
                        @foreach ([50, 100, 200, 500, 1000] as $n)
                            <option value="{{ $n }}" {{ (int) $lines === $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded text-sm">
                    <i class="fas fa-sync-alt mr-1"></i> Atualizar
                </button>
            </form>
        </div>

        <div class="bg-gray-900 rounded shadow overflow-hidden">
            <div class="px-4 py-2 bg-gray-800 text-gray-400 text-xs flex items-center justify-between">
                <span><i class="fas fa-terminal mr-1"></i> Apache Error Log</span>
                <button onclick="copyToClipboard(document.getElementById('log-content').textContent)"
                        class="hover:text-white transition-colors">
                    <i class="fas fa-copy mr-1"></i> Copiar
                </button>
            </div>
            <pre id="log-content" class="p-4 text-sm font-mono text-green-400 overflow-x-auto max-h-[600px] overflow-y-auto leading-relaxed" style="white-space: pre-wrap; word-break: break-all;">{{ $logContent ?: 'Nenhuma entrada encontrada.' }}</pre>
        </div>
    @endif
@endsection
