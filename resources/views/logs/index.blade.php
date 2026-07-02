@extends('layouts.app')

@section('title', 'Logs do Apache')

@section('content')
    <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight"><i class="fas fa-file-alt mr-2 text-blue-500"></i>Logs do Apache</h1>
            @if ($logPath)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-mono">{{ $logPath }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('dashboard') }}" class="btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    @if (!$logPath || !file_exists($logPath))
        <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50/80 backdrop-blur-sm px-4 py-3 dark:border-amber-800 dark:bg-amber-950/30">
            <span class="flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-600 shrink-0 dark:bg-amber-400/10 dark:text-amber-400">
                <i class="fas fa-exclamation-triangle text-xs"></i>
            </span>
            <div class="flex-1 text-sm text-amber-800 dark:text-amber-200">
                Caminho do log não configurado ou arquivo não encontrado.
                <a href="{{ route('settings.index') }}" class="underline font-medium hover:text-amber-900 dark:hover:text-amber-100 ml-1">Configurar em Settings</a>
            </div>
        </div>
    @else
        <div class="card p-4 mb-6">
            <form method="GET" action="{{ route('logs.index') }}" class="flex items-center gap-4 flex-wrap">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 dark:text-gray-400">Nível:</label>
                    <select name="level" class="input py-1.5 w-auto" onchange="this.form.submit()">
                        @foreach ($levels as $l)
                            <option value="{{ $l }}" {{ $level === $l ? 'selected' : '' }}>{{ ucfirst($l) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 dark:text-gray-400">Linhas:</label>
                    <select name="lines" class="input py-1.5 w-auto" onchange="this.form.submit()">
                        @foreach ([50, 100, 200, 500, 1000] as $n)
                            <option value="{{ $n }}" {{ (int) $lines === $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary btn-sm">
                    <i class="fas fa-sync-alt"></i> Atualizar
                </button>
            </form>
        </div>

        <div class="rounded-xl overflow-hidden border border-gray-700/50 shadow-lg">
            <div class="px-4 py-2.5 bg-gray-800 dark:bg-gray-900 text-gray-400 text-xs flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <i class="fas fa-terminal mr-1"></i> Apache Error Log
                </span>
                <button onclick="copyToClipboard(document.getElementById('log-content').textContent)"
                        class="hover:text-white transition-colors flex items-center gap-1.5">
                    <i class="fas fa-copy"></i> Copiar
                </button>
            </div>
            <pre id="log-content" class="p-4 text-sm font-mono text-emerald-400 bg-gray-950 overflow-x-auto max-h-[600px] overflow-y-auto leading-relaxed" style="white-space: pre-wrap; word-break: break-all;">{{ $logContent ?: 'Nenhuma entrada encontrada.' }}</pre>
        </div>
    @endif
@endsection
