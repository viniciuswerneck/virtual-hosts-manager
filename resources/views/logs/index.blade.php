@extends('layouts.app')

@section('title', 'Logs do Apache')

@section('content')
    <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">
                <i class="fas fa-file-alt mr-2 text-blue-500"></i>Logs do Apache
            </h1>
            @if ($logPath)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-mono text-xs">{{ $logPath }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            <button id="live-toggle" onclick="toggleLive()" class="btn btn-primary btn-sm">
                <i class="fas fa-play"></i> <span>Auto-Refresh</span>
            </button>
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
        @php
            $severityConfig = [
                'error' => ['label' => 'Erro', 'color' => 'red', 'bg' => 'bg-red-50 dark:bg-red-500/10', 'text' => 'text-red-700 dark:text-red-300', 'badge' => 'bg-gray-100 text-red-700 dark:bg-gray-700 dark:text-red-300', 'border' => 'border-l-red-500', 'icon' => 'fa-times-circle'],
                'warn' => ['label' => 'Aviso', 'color' => 'amber', 'bg' => 'bg-amber-50 dark:bg-amber-500/10', 'text' => 'text-amber-700 dark:text-amber-300', 'badge' => 'bg-gray-100 text-amber-700 dark:bg-gray-700 dark:text-amber-300', 'border' => 'border-l-amber-500', 'icon' => 'fa-exclamation-triangle'],
                'notice' => ['label' => 'Notícia', 'color' => 'blue', 'bg' => 'bg-blue-50/50 dark:bg-blue-500/10', 'text' => 'text-blue-700 dark:text-blue-300', 'badge' => 'bg-gray-100 text-blue-700 dark:bg-gray-700 dark:text-blue-300', 'border' => 'border-l-blue-500', 'icon' => 'fa-info-circle'],
                'info' => ['label' => 'Info', 'color' => 'gray', 'bg' => 'dark:bg-gray-700/30', 'text' => 'text-gray-600 dark:text-gray-300', 'badge' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', 'border' => 'border-l-gray-400 dark:border-l-gray-600', 'icon' => 'fa-info-circle'],
                'debug' => ['label' => 'Debug', 'color' => 'gray', 'bg' => 'dark:bg-gray-700/20', 'text' => 'text-gray-500 dark:text-gray-400', 'badge' => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400', 'border' => 'border-l-gray-400 dark:border-l-gray-600', 'icon' => 'fa-bug'],
            ];
            $totalFiltered = array_sum($levelCounts);
        @endphp

        <div class="flex flex-nowrap gap-1.5 mb-4">
            <div class="card p-1.5 sm:p-2 flex items-center gap-1.5 flex-1 min-w-0">
                <i class="fas fa-list text-gray-400 dark:text-gray-500 text-xs w-3 text-center shrink-0"></i>
                <span class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 truncate">Exibidos</span>
                <span class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-100 shrink-0">{{ $totalFiltered }}</span>
            </div>
            @foreach (['error' => ['icon' => 'fa-times-circle text-red-400', 'label' => 'Erros'], 'warn' => ['icon' => 'fa-exclamation-triangle text-amber-400', 'label' => 'Avisos'], 'notice' => ['icon' => 'fa-info-circle text-blue-400', 'label' => 'Notícias'], 'info' => ['icon' => 'fa-info-circle text-gray-400', 'label' => 'Info']] as $key => $cfg)
                <div class="card p-1.5 sm:p-2 flex items-center gap-1.5 flex-1 min-w-0">
                    <i class="fas {{ $cfg['icon'] }} text-xs w-3 text-center shrink-0"></i>
                    <span class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400 truncate">{{ $cfg['label'] }}</span>
                    <span class="text-sm sm:text-base font-bold {{ $severityConfig[$key]['text'] }} shrink-0">{{ $levelCounts[$key] ?? 0 }}</span>
                </div>
            @endforeach
        </div>

        <div class="card p-4 mb-4">
            <form method="GET" action="{{ route('logs.index') }}" class="flex items-center gap-3 flex-wrap" id="log-filters">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar no log..."
                           class="input pl-9 py-1.5">
                </div>
                <select name="level" class="input py-1.5 w-auto" onchange="document.getElementById('log-filters').submit()">
                    @foreach ($levels as $l)
                        <option value="{{ $l }}" {{ $level === $l ? 'selected' : '' }}>
                            {{ $l === 'all' ? 'Todos os níveis' : ucfirst($l) }}
                        </option>
                    @endforeach
                </select>
                <select name="lines" class="input py-1.5 w-auto" onchange="document.getElementById('log-filters').submit()">
                    @foreach ([50, 100, 200, 500, 1000] as $n)
                        <option value="{{ $n }}" {{ $lines === $n ? 'selected' : '' }}>{{ $n }} linhas</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary btn-sm">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                @if ($level !== 'all' || $search)
                    <a href="{{ route('logs.index') }}" class="btn-ghost btn-sm text-gray-500">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                @endif
            </form>
        </div>

        <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span id="live-indicator" class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        <i class="fas fa-terminal mr-1.5"></i>Apache Error Log
                    </span>
                    <span id="live-status" class="text-xs text-gray-400 dark:text-gray-500"></span>
                </div>
                <div class="flex items-center gap-3">
                    <span id="log-timestamp" class="text-xs text-gray-400 dark:text-gray-500"></span>
                    <button onclick="copyAllLogs()"
                            class="text-xs text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors flex items-center gap-1">
                        <i class="fas fa-copy"></i> Copiar tudo
                    </button>
                </div>
            </div>

            @if (empty($logEntries))
                <div class="p-12 text-center text-sm text-gray-400 dark:text-gray-500">
                    <i class="fas fa-inbox text-3xl mb-3 block text-gray-300 dark:text-gray-600"></i>
                    Nenhuma entrada encontrada.
                    @if ($level !== 'all' || $search)
                        <br><a href="{{ route('logs.index') }}" class="text-blue-500 hover:underline mt-1 inline-block">Limpar filtros</a>
                    @endif
                </div>
            @else
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50 max-h-[600px] overflow-y-auto" id="log-entries">
                    @foreach ($logEntries as $entry)
                        @php
                            $sev = $severityConfig[$entry['level']] ?? $severityConfig['info'];
                            $hasDetail = $entry['module'] || $entry['pid'] || $entry['client'];
                        @endphp
                        <div class="border-l-4 {{ $sev['border'] }} {{ $sev['bg'] }} px-4 py-2.5 hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold shrink-0 mt-0.5 {{ $sev['badge'] }}">
                                    <i class="fas {{ $sev['icon'] }}"></i>
                                    {{ $sev['label'] }}
                                </span>
                                @if ($entry['ts_formatted'])
                                    <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0 mt-0.5" title="{{ $entry['ts_tooltip'] }}">{{ $entry['ts_formatted'] }}</span>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 dark:text-gray-200 leading-relaxed">
                                        @if ($search)
                                            {!! preg_replace(
                                                '/(' . preg_quote($search, '/') . ')/iu',
                                                '<mark class="bg-yellow-200 dark:bg-yellow-500/30 text-inherit rounded px-0.5">$1</mark>',
                                                e($entry['message'])
                                            ) !!}
                                        @else
                                            {{ $entry['message'] }}
                                        @endif
                                    </p>
                                    @if ($hasDetail)
                                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-400 dark:text-gray-500">
                                            @if ($entry['module'])
                                                <span><i class="fas fa-puzzle-piece mr-0.5"></i>{{ $entry['module'] }}</span>
                                            @endif
                                            @if ($entry['pid'])
                                                <span><i class="fas fa-microchip mr-0.5"></i>PID {{ $entry['pid'] }}</span>
                                            @endif
                                            @if ($entry['client'])
                                                <span><i class="fas fa-network-wired mr-0.5"></i>{{ $entry['client'] }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <button onclick="copyLogLine(this, {{ $loop->index }})"
                                        class="shrink-0 text-xs text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 opacity-0 hover:opacity-100 transition-all"
                                        title="Copiar linha">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <script>
        var liveInterval = null;
        var isLive = false;
        var allLogEntries = @json($logEntries ?? []);

        function toggleLive() {
            var btn = document.getElementById('live-toggle');
            var indicator = document.getElementById('live-indicator');
            var status = document.getElementById('live-status');

            if (isLive) {
                clearInterval(liveInterval);
                liveInterval = null;
                isLive = false;
                btn.innerHTML = '<i class="fas fa-play"></i> <span>Auto-Refresh</span>';
                btn.className = 'btn btn-primary btn-sm';
                indicator.className = 'w-2 h-2 rounded-full bg-emerald-500';
                status.textContent = '';
                return;
            }

            isLive = true;
            btn.innerHTML = '<i class="fas fa-stop"></i> <span>Parar</span>';
            btn.className = 'btn btn-warning btn-sm';
            indicator.className = 'w-2 h-2 rounded-full bg-green-400 animate-pulse';
            status.textContent = 'AO VIVO';
            fetchLogs();
            liveInterval = setInterval(fetchLogs, 3000);
        }

        function fetchLogs() {
            var params = new URLSearchParams();
            var level = document.querySelector('select[name="level"]');
            var lines = document.querySelector('select[name="lines"]');
            var search = document.querySelector('input[name="search"]');
            if (level) params.set('level', level.value);
            if (lines) params.set('lines', lines.value);
            if (search) params.set('search', search.value);

            fetch('{{ route('logs.stream') }}?' + params.toString())
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.entries) {
                        allLogEntries = data.entries;
                        renderEntries(data.entries, data.levelCounts || {});
                    }
                    var ts = document.getElementById('log-timestamp');
                    ts.textContent = new Date().toLocaleTimeString('pt-BR');
                })
                .catch(function() {});
        }

        function renderEntries(entries, counts) {
            var container = document.getElementById('log-entries');
            if (!container) return;

            var search = document.querySelector('input[name="search"]');
            var searchTerm = search ? search.value : '';

            if (!entries || entries.length === 0) {
                container.innerHTML = '<div class="p-12 text-center text-sm text-gray-400 dark:text-gray-500">' +
                    '<i class="fas fa-inbox text-3xl mb-3 block text-gray-300 dark:text-gray-600"></i>' +
                    'Nenhuma entrada encontrada.</div>';
                return;
            }

            var html = '';
            entries.forEach(function(entry, idx) {
                var sev = getSeverity(entry.level);
                var hasDetail = entry.module || entry.pid || entry.client;

                var message = escapeHtml(entry.message);
                if (searchTerm) {
                    var re = new RegExp('(' + escapeRegExp(searchTerm) + ')', 'gi');
                    message = message.replace(re, '<mark class="bg-yellow-200 dark:bg-yellow-500/30 text-inherit rounded px-0.5">$1</mark>');
                }

                html += '<div class="border-l-4 ' + sev.border + ' ' + sev.bg + ' px-4 py-2.5 hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">' +
                    '<div class="flex items-start gap-3">' +
                    '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold shrink-0 mt-0.5 ' + sev.badge + '">' +
                    '<i class="fas ' + sev.icon + '"></i> ' + sev.label + '</span>';

                if (entry.ts_formatted) {
                    var tsTitle = entry.ts_tooltip ? ' title="' + escapeHtml(entry.ts_tooltip) + '"' : '';
                    html += '<span class="text-xs text-gray-400 dark:text-gray-500 shrink-0 mt-0.5"' + tsTitle + '>' + escapeHtml(entry.ts_formatted) + '</span>';
                }

                html += '<div class="flex-1 min-w-0">' +
                    '<p class="text-sm text-gray-700 dark:text-gray-200 leading-relaxed">' + message + '</p>';

                if (hasDetail) {
                    html += '<div class="flex items-center gap-3 mt-1 text-xs text-gray-400 dark:text-gray-500">';
                    if (entry.module) html += '<span><i class="fas fa-puzzle-piece mr-0.5"></i>' + escapeHtml(entry.module) + '</span>';
                    if (entry.pid) html += '<span><i class="fas fa-microchip mr-0.5"></i>PID ' + escapeHtml(entry.pid) + '</span>';
                    if (entry.client) html += '<span><i class="fas fa-network-wired mr-0.5"></i>' + escapeHtml(entry.client) + '</span>';
                    html += '</div>';
                }

                html += '</div>' +
                    '<button onclick="copyLogLine(this, ' + idx + ')" class="shrink-0 text-xs text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 opacity-0 hover:opacity-100 transition-all" title="Copiar linha">' +
                    '<i class="fas fa-copy"></i></button>' +
                    '</div></div>';
            });

            container.innerHTML = html;
        }

        function getSeverity(level) {
            var map = {
                'error': { label: 'Erro', badge: 'bg-gray-100 text-red-700 dark:bg-gray-700 dark:text-red-300', border: 'border-l-red-500', bg: 'bg-red-50 dark:bg-red-500/5', text: 'text-red-700 dark:text-red-300', icon: 'fa-times-circle' },
                'warn': { label: 'Aviso', badge: 'bg-gray-100 text-amber-700 dark:bg-gray-700 dark:text-amber-300', border: 'border-l-amber-500', bg: 'bg-amber-50 dark:bg-amber-500/5', text: 'text-amber-700 dark:text-amber-300', icon: 'fa-exclamation-triangle' },
                'notice': { label: 'Notícia', badge: 'bg-gray-100 text-blue-700 dark:bg-gray-700 dark:text-blue-300', border: 'border-l-blue-500', bg: 'bg-blue-50/50 dark:bg-blue-500/5', text: 'text-blue-700 dark:text-blue-300', icon: 'fa-info-circle' },
                'info': { label: 'Info', badge: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', border: 'border-l-gray-400 dark:border-l-gray-600', bg: '', text: 'text-gray-600 dark:text-gray-400', icon: 'fa-info-circle' },
                'debug': { label: 'Debug', badge: 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400', border: 'border-l-gray-400 dark:border-l-gray-600', bg: '', text: 'text-gray-500 dark:text-gray-500', icon: 'fa-bug' },
            };
            return map[level] || map['info'];
        }

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str || '';
            return div.innerHTML;
        }

        function escapeRegExp(str) {
            return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function copyLogLine(btn, idx) {
            var entry = allLogEntries[idx];
            if (!entry) return;
            var text = entry.raw || entry.message;
            copyToClipboard(text);
            var icon = btn.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-check';
                setTimeout(function() { icon.className = 'fas fa-copy'; }, 1500);
            }
        }

        function copyAllLogs() {
            var text = allLogEntries.map(function(e) { return e.raw || e.message; }).join('\n');
            copyToClipboard(text);
        }

        window.addEventListener('beforeunload', function() {
            if (liveInterval) clearInterval(liveInterval);
        });
    </script>
@endsection