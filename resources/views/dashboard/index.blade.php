@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="flex items-start justify-between mb-8 gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">Dashboard</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Visão geral dos seus virtual hosts</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('virtual-hosts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Novo Virtual Host
            </a>
            <form action="{{ route('virtual-hosts.restart') }}" method="POST" class="restart-form"
                  onsubmit="return confirm('Reiniciar o Apache agora?')">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-redo-alt"></i> Reiniciar Apache
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-8">
        <div class="card p-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                    <i class="fas fa-server"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100 stat-count" data-target="{{ $totalVhosts }}">0</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total</p>
                </div>
            </div>
        </div>
        <div class="card p-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100 stat-count" data-target="{{ $activeVhosts }}">0</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Ativos</p>
                </div>
            </div>
            @if ($totalVhosts > 0)
                <div class="mt-2 flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                    <div class="flex-1 h-1 rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-1 rounded-full bg-emerald-500 transition-all duration-700" style="width: {{ round(($activeVhosts / $totalVhosts) * 100) }}%"></div>
                    </div>
                    <span class="font-medium">{{ round(($activeVhosts / $totalVhosts) * 100) }}%</span>
                </div>
            @endif
        </div>
        <div class="card p-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                    <i class="fas fa-lock"></i>
                </div>
                <div>
                    <p class="text-xl font-bold text-gray-900 dark:text-gray-100 stat-count" data-target="{{ $sslCount }}">0</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Com SSL</p>
                </div>
            </div>
            @if ($totalVhosts > 0)
                <div class="mt-2 flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                    <div class="flex-1 h-1 rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-1 rounded-full bg-violet-500 transition-all duration-700" style="width: {{ round(($sslCount / $totalVhosts) * 100) }}%"></div>
                    </div>
                    <span class="font-medium">{{ round(($sslCount / $totalVhosts) * 100) }}%</span>
                </div>
            @endif
        </div>
        <div class="card p-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg {{ $apacheOnline ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' }}">
                    <i class="fas {{ $apacheOnline ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                </div>
                <div>
                    <p class="text-xl font-bold {{ $apacheOnline ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">{{ $apacheOnline ? 'Online' : 'Offline' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Apache</p>
                </div>
            </div>
            @if ($apacheOnline)
                <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500 text-right">{{ $apachePidCount }} processo(s)</p>
            @endif
        </div>
    </div>

    @if ($sslWithoutCert->isNotEmpty() || $notInApache->isNotEmpty())
        <div class="mb-8 space-y-3">
            @if ($sslWithoutCert->isNotEmpty())
                <div class="flex items-start gap-3 bg-amber-50/80 backdrop-blur-sm border border-amber-200 rounded-xl px-4 py-3 dark:bg-amber-950/30 dark:border-amber-800">
                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-600 shrink-0 dark:bg-amber-400/10 dark:text-amber-400">
                        <i class="fas fa-certificate text-xs"></i>
                    </span>
                    <div class="flex-1 text-sm text-amber-800 dark:text-amber-200">
                        <span class="font-medium">{{ $sslWithoutCert->count() }} vhost(s)</span> com SSL ativado mas sem certificado:
                        @foreach ($sslWithoutCert as $v)
                            <a href="{{ route('virtual-hosts.show', $v) }}" class="underline font-medium hover:text-amber-900 dark:hover:text-amber-100">{{ $v->server_name }}</a>@if (!$loop->last), @endif
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($notInApache->isNotEmpty())
                <div class="flex items-start gap-3 bg-amber-50/80 backdrop-blur-sm border border-amber-200 rounded-xl px-4 py-3 dark:bg-amber-950/30 dark:border-amber-800">
                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-600 shrink-0 dark:bg-amber-400/10 dark:text-amber-400">
                        <i class="fas fa-sync-alt text-xs"></i>
                    </span>
                    <div class="flex-1 text-sm text-amber-800 dark:text-amber-200">
                        <span class="font-medium">{{ $notInApache->count() }} vhost(s) ativos</span> não encontrados no Apache:
                        @foreach ($notInApache as $v)
                            <a href="{{ route('virtual-hosts.show', $v) }}" class="underline font-medium hover:text-amber-900 dark:hover:text-amber-100">{{ $v->server_name }}</a>@if (!$loop->last), @endif
                        @endforeach
                        <a href="{{ route('virtual-hosts.sync') }}" class="ml-2 btn-secondary btn-xs">Sincronizar</a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
        <div class="lg:col-span-2 card animate-fade-in">
            <div class="card-header">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <i class="fas fa-heartbeat text-rose-500"></i>
                    Status do Sistema
                </h2>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                <div class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 shrink-0">
                        <i class="fab fa-php"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 mb-2">PHP</p>
                        <div class="space-y-1">
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">Versão</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $phpVersion }}</span>
                            </div>
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">Memory</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $phpMemoryLimit }}</span>
                            </div>
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">Max Exec</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $phpMaxExecTime }}s</span>
                            </div>
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">Upload</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $phpUploadMax }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 shrink-0">
                        <i class="fas fa-chart-pie"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 mb-2">PHP nos Vhosts</p>
                        <div class="space-y-2">
                            @forelse ($phpVersionTotal as $pv)
                                @php $pct = $totalVhosts > 0 ? round(($pv->total / $totalVhosts) * 100) : 0; @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-0.5">
                                        <span class="font-medium text-gray-600 dark:text-gray-400">PHP {{ $pv->php_version }}</span>
                                        <span class="text-gray-400 dark:text-gray-500">{{ $pv->total }}</span>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-700">
                                        <div class="h-1.5 rounded-full bg-indigo-400 transition-all duration-700" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 dark:text-gray-500">Nenhum vhost com PHP específico</p>
                            @endforelse
                            @if ($phpNullCount > 0)
                                <div class="flex items-center justify-between text-sm pt-1.5 border-t border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-400 dark:text-gray-500">Padrão do Apache</span>
                                    <span class="font-medium text-gray-500 dark:text-gray-400">{{ $phpNullCount }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 shrink-0">
                        <i class="fas fa-server"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 mb-2">Apache</p>
                        <div class="space-y-1">
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">Status</span>
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium {{ $apacheOnline ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $apacheOnline ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    {{ $apacheOnline ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                            @if ($apacheOnline)
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">Processos</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $apachePidCount }}</span>
                                </div>
                            @endif
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">Config</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $apacheConfigTest['success'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400' }}">
                                    <i class="fas {{ $apacheConfigTest['success'] ? 'fa-check' : 'fa-times' }}"></i>
                                    {{ $apacheConfigTest['success'] ? 'OK' : 'Erro' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-400 shrink-0">
                        <i class="fas fa-hdd"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 mb-2">Disco</p>
                        <div class="text-sm">
                            <div class="flex items-baseline justify-between mb-1.5">
                                <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">Uso</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $diskPercent }}%</span>
                            </div>
                            @php $diskColor = $diskPercent > 90 ? 'bg-red-500' : ($diskPercent > 70 ? 'bg-amber-500' : 'bg-emerald-500'); @endphp
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-700">
                                <div class="h-2 rounded-full transition-all duration-700 {{ $diskColor }}" style="width: {{ $diskPercent }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-500 mt-1">
                                <span>{{ round($diskUsed / 1073741824, 1) }} GB usados</span>
                                <span>{{ round($diskTotal / 1073741824, 1) }} GB total</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card animate-fade-in">
            <div class="card-header flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <i class="fas fa-history text-emerald-500"></i>
                    Atividades
                </h2>
                <a href="{{ route('activity-logs.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">Ver todas</a>
            </div>
            @if ($recentActivities->isEmpty())
                <div class="px-6 py-12 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 text-gray-400 mb-3 dark:bg-gray-700">
                        <i class="fas fa-history text-xl"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma atividade registrada.</p>
                </div>
            @else
                <div class="px-5">
                    @php
                        $actionCfg = [
                            'created' => ['dot' => 'bg-emerald-500', 'icon' => 'fa-plus-circle', 'color' => 'text-emerald-500'],
                            'updated' => ['dot' => 'bg-blue-500', 'icon' => 'fa-pen', 'color' => 'text-blue-500'],
                            'deleted' => ['dot' => 'bg-red-500', 'icon' => 'fa-trash', 'color' => 'text-red-500'],
                            'toggled' => ['dot' => 'bg-amber-500', 'icon' => 'fa-toggle-on', 'color' => 'text-amber-500'],
                            'restarted' => ['dot' => 'bg-amber-500', 'icon' => 'fa-redo-alt', 'color' => 'text-amber-500'],
                            'synced' => ['dot' => 'bg-blue-500', 'icon' => 'fa-sync-alt', 'color' => 'text-blue-500'],
                            'exported' => ['dot' => 'bg-violet-500', 'icon' => 'fa-file-export', 'color' => 'text-violet-500'],
                            'imported' => ['dot' => 'bg-violet-500', 'icon' => 'fa-file-import', 'color' => 'text-violet-500'],
                            'cert_created' => ['dot' => 'bg-emerald-500', 'icon' => 'fa-certificate', 'color' => 'text-emerald-500'],
                            'batch_toggled' => ['dot' => 'bg-amber-500', 'icon' => 'fa-check-double', 'color' => 'text-amber-500'],
                            'batch_deleted' => ['dot' => 'bg-red-500', 'icon' => 'fa-trash-alt', 'color' => 'text-red-500'],
                        ];
                    @endphp
                    @foreach ($recentActivities as $log)
                        @php $cfg = $actionCfg[$log->action] ?? ['dot' => 'bg-gray-400', 'icon' => 'fa-circle', 'color' => 'text-gray-400']; @endphp
                        <div class="flex items-start gap-3 py-2.5 border-b border-gray-50 dark:border-gray-700/30 last:border-0">
                            <span class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $cfg['dot'] }}"></span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <i class="fas {{ $cfg['icon'] }} {{ $cfg['color'] }} text-xs"></i>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 truncate font-medium">{{ $log->description }}</p>
                                </div>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <i class="fas fa-clock text-blue-500"></i>
                    Últimos Vhosts
                </h2>
                <a href="{{ route('virtual-hosts.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">Ver todos</a>
            </div>
            @if ($recentVhosts->isEmpty())
                <div class="px-6 py-12 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 text-gray-400 mb-3 dark:bg-gray-700">
                        <i class="fas fa-globe text-xl"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum vhost cadastrado ainda.</p>
                    <a href="{{ route('virtual-hosts.create') }}" class="btn-primary btn-sm mt-3">
                        <i class="fas fa-plus-circle"></i> Criar o primeiro
                    </a>
                </div>
            @else
                <div class="divide-y divide-gray-50 dark:divide-gray-700/30">
                    @foreach ($recentVhosts as $v)
                        <div class="flex items-center justify-between px-6 py-3 hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0 {{ $v->active ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                <div class="min-w-0">
                                    <a href="{{ route('virtual-hosts.show', $v) }}" class="font-medium text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $v->server_name }}
                                    </a>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $v->document_root }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs shrink-0">
                                @if ($v->php_version)
                                    <span class="bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300 px-2 py-0.5 rounded-full font-medium"><i class="fab fa-php mr-0.5"></i>{{ $v->php_version }}</span>
                                @endif
                                @if ($v->template)
                                    <span class="bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 px-2 py-0.5 rounded-full font-medium">{{ $v->template }}</span>
                                @endif
                                @if ($v->ssl_enabled)
                                    <span class="bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 px-2 py-0.5 rounded-full"><i class="fas fa-lock"></i></span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <i class="fas fa-bolt text-amber-500"></i>
                    Ações Rápidas
                </h2>
            </div>
            <div class="p-4 grid grid-cols-2 gap-2">
                <a href="{{ route('virtual-hosts.create') }}"
                   class="flex items-center gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl p-3.5 text-sm font-medium text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500 transition-all">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/20 dark:text-blue-300">
                        <i class="fas fa-plus-circle"></i>
                    </span>
                    Novo Vhost
                </a>
                <a href="{{ route('virtual-hosts.index') }}"
                   class="flex items-center gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl p-3.5 text-sm font-medium text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500 transition-all">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-500/20 dark:text-sky-300">
                        <i class="fas fa-list"></i>
                    </span>
                    Listar Vhosts
                </a>
                <a href="{{ route('virtual-hosts.sync') }}"
                   class="flex items-center gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl p-3.5 text-sm font-medium text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500 transition-all"
                   onclick="return confirm('Importar todos os hosts do Apache para o banco?')">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300">
                        <i class="fas fa-sync-alt"></i>
                    </span>
                    Sincronizar
                </a>
                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl p-3.5 text-sm font-medium text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500 transition-all">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300">
                        <i class="fas fa-cog"></i>
                    </span>
                    Configurações
                </a>
                <a href="{{ route('logs.index') }}"
                   class="flex items-center gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl p-3.5 text-sm font-medium text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500 transition-all">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 dark:bg-red-500/20 dark:text-red-300">
                        <i class="fas fa-file-alt"></i>
                    </span>
                    Logs Apache
                </a>
                <a href="{{ route('activity-logs.index') }}"
                   class="flex items-center gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl p-3.5 text-sm font-medium text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500 transition-all">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-teal-50 text-teal-600 dark:bg-teal-500/20 dark:text-teal-300">
                        <i class="fas fa-history"></i>
                    </span>
                    Histórico
                </a>
                <a href="{{ route('file-manager.index') }}"
                   class="flex items-center gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl p-3.5 text-sm font-medium text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500 transition-all">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 dark:bg-cyan-500/20 dark:text-cyan-300">
                        <i class="fas fa-folder-open"></i>
                    </span>
                    Arquivos
                </a>
                @php
                    $phpmyadmin = config('virtualhosts.phpmyadmin_url');
                    $pmaUser = config('virtualhosts.phpmyadmin_user');
                    $pmaPass = config('virtualhosts.phpmyadmin_password');
                    $pmaAutoUrl = $phpmyadmin;
                    if ($pmaUser) {
                        $pmaAutoUrl .= (str_contains($phpmyadmin, '?') ? '&' : '?') . 'username=' . urlencode($pmaUser);
                        if ($pmaPass) {
                            $pmaAutoUrl .= '&password=' . urlencode($pmaPass);
                        }
                    }
                @endphp
                @if ($phpmyadmin)
                    <a href="{{ $pmaAutoUrl }}" target="_blank"
                       class="flex items-center gap-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl p-3.5 text-sm font-medium text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500 transition-all">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-500/20 dark:text-purple-300">
                            <i class="fas fa-database"></i>
                        </span>
                        phpMyAdmin
                        @if ($pmaUser)
                            <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:inline">({{ $pmaUser }})</span>
                        @endif
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var counters = document.querySelectorAll('.stat-count');
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    animateCounter(el, parseInt(el.dataset.target, 10), 700);
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.3 });
        counters.forEach(function (el) { observer.observe(el); });

        function animateCounter(el, target, duration) {
            var start = 0, startTime = null;
            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                var progress = Math.min((timestamp - startTime) / duration, 1);
                el.textContent = Math.floor((1 - Math.pow(1 - progress, 3)) * target);
                if (progress < 1) requestAnimationFrame(step);
                else el.textContent = target;
            }
            requestAnimationFrame(step);
        }
    });
</script>