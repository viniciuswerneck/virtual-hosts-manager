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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card p-5 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-sm">
                <i class="fas fa-server"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalVhosts }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total de Vhosts</p>
            </div>
        </div>
        <div class="card p-5 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-sm">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $activeVhosts }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Ativos</p>
            </div>
        </div>
        <div class="card p-5 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 text-white shadow-sm">
                <i class="fas fa-lock"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $sslCount }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Com SSL</p>
            </div>
        </div>
        <div class="card p-5 flex items-center gap-4 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl text-white shadow-sm {{ $apacheOnline ? 'bg-gradient-to-br from-emerald-500 to-emerald-600' : 'bg-gradient-to-br from-red-500 to-red-600' }}">
                <i class="fas {{ $apacheOnline ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $apacheOnline ? 'Online' : 'Offline' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Apache</p>
            </div>
        </div>
    </div>

    @if ($sslWithoutCert->isNotEmpty() || $notInApache->isNotEmpty())
    <div class="mb-8 space-y-3">
        @if ($sslWithoutCert->isNotEmpty())
            <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50/80 backdrop-blur-sm px-4 py-3 dark:border-amber-800 dark:bg-amber-950/30">
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
            <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50/80 backdrop-blur-sm px-4 py-3 dark:border-amber-800 dark:bg-amber-950/30">
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100"><i class="fas fa-clock text-blue-500 mr-2"></i>Últimos Vhosts</h2>
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
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach ($recentVhosts as $v)
                        <div class="px-6 py-3.5 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0 {{ $v->active ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                <div class="min-w-0">
                                    <a href="{{ route('virtual-hosts.show', $v) }}" class="font-medium text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $v->server_name }}
                                    </a>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $v->document_root }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500 shrink-0">
                                @if ($v->template)
                                    <span class="badge-gray">{{ $v->template }}</span>
                                @endif
                                @if ($v->ssl_enabled)
                                    <i class="fas fa-lock text-emerald-500" title="SSL"></i>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100"><i class="fas fa-bolt text-amber-500 mr-2"></i>Ações Rápidas</h2>
            </div>
            <div class="p-5 grid grid-cols-2 gap-3">
                <a href="{{ route('virtual-hosts.create') }}"
                   class="flex items-center gap-3 rounded-xl bg-gradient-to-br from-blue-50 to-blue-50/50 text-blue-700 p-4 text-sm font-medium hover:from-blue-100 hover:to-blue-50 transition-all duration-150 ring-1 ring-blue-600/5 dark:from-blue-950/30 dark:to-blue-950/10 dark:text-blue-400 dark:ring-blue-400/10 dark:hover:from-blue-950/50">
                    <i class="fas fa-plus-circle text-lg"></i>
                    <span>Novo Vhost</span>
                </a>
                <a href="{{ route('virtual-hosts.index') }}"
                   class="flex items-center gap-3 rounded-xl bg-gradient-to-br from-sky-50 to-sky-50/50 text-sky-700 p-4 text-sm font-medium hover:from-sky-100 hover:to-sky-50 transition-all duration-150 ring-1 ring-sky-600/5 dark:from-sky-950/30 dark:to-sky-950/10 dark:text-sky-400 dark:ring-sky-400/10 dark:hover:from-sky-950/50">
                    <i class="fas fa-list text-lg"></i>
                    <span>Listar Vhosts</span>
                </a>
                <a href="{{ route('virtual-hosts.sync') }}"
                   class="flex items-center gap-3 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-50/50 text-emerald-700 p-4 text-sm font-medium hover:from-emerald-100 hover:to-emerald-50 transition-all duration-150 ring-1 ring-emerald-600/5 dark:from-emerald-950/30 dark:to-emerald-950/10 dark:text-emerald-400 dark:ring-emerald-400/10 dark:hover:from-emerald-950/50"
                   onclick="return confirm('Importar todos os hosts do Apache para o banco?')">
                    <i class="fas fa-sync-alt text-lg"></i>
                    <span>Sincronizar</span>
                </a>
                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-3 rounded-xl bg-gradient-to-br from-gray-100 to-gray-50 text-gray-700 p-4 text-sm font-medium hover:from-gray-200 hover:to-gray-100 transition-all duration-150 ring-1 ring-gray-600/5 dark:from-gray-800 dark:to-gray-800/50 dark:text-gray-300 dark:ring-gray-500/10 dark:hover:from-gray-700">
                    <i class="fas fa-cog text-lg"></i>
                    <span>Configurações</span>
                </a>
                <a href="{{ route('logs.index') }}"
                   class="flex items-center gap-3 rounded-xl bg-gradient-to-br from-red-50 to-red-50/50 text-red-700 p-4 text-sm font-medium hover:from-red-100 hover:to-red-50 transition-all duration-150 ring-1 ring-red-600/5 dark:from-red-950/30 dark:to-red-950/10 dark:text-red-400 dark:ring-red-400/10 dark:hover:from-red-950/50">
                    <i class="fas fa-file-alt text-lg"></i>
                    <span>Logs Apache</span>
                </a>
                <a href="{{ route('activity-logs.index') }}"
                   class="flex items-center gap-3 rounded-xl bg-gradient-to-br from-teal-50 to-teal-50/50 text-teal-700 p-4 text-sm font-medium hover:from-teal-100 hover:to-teal-50 transition-all duration-150 ring-1 ring-teal-600/5 dark:from-teal-950/30 dark:to-teal-950/10 dark:text-teal-400 dark:ring-teal-400/10 dark:hover:from-teal-950/50">
                    <i class="fas fa-history text-lg"></i>
                    <span>Histórico</span>
                </a>
                <a href="{{ route('file-manager.index') }}"
                   class="flex items-center gap-3 rounded-xl bg-gradient-to-br from-cyan-50 to-cyan-50/50 text-cyan-700 p-4 text-sm font-medium hover:from-cyan-100 hover:to-cyan-50 transition-all duration-150 ring-1 ring-cyan-600/5 dark:from-cyan-950/30 dark:to-cyan-950/10 dark:text-cyan-400 dark:ring-cyan-400/10 dark:hover:from-cyan-950/50">
                    <i class="fas fa-folder-open text-lg"></i>
                    <span>Arquivos</span>
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
                       class="flex items-center gap-3 rounded-xl bg-gradient-to-br from-purple-50 to-purple-50/50 text-purple-700 p-4 text-sm font-medium hover:from-purple-100 hover:to-purple-50 transition-all duration-150 ring-1 ring-purple-600/5 dark:from-purple-950/30 dark:to-purple-950/10 dark:text-purple-400 dark:ring-purple-400/10 dark:hover:from-purple-950/50"
                       title="Clique para abrir com login automático">
                        <i class="fas fa-database text-lg"></i>
                        <span>phpMyAdmin</span>
                        @if ($pmaUser)
                            <span class="text-xs opacity-60 hidden sm:inline">({{ $pmaUser }})</span>
                        @endif
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
