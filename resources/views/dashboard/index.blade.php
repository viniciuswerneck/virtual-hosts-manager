@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
        <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-tachometer-alt mr-2 text-indigo-600"></i>Dashboard</h1>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('virtual-hosts.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm inline-flex items-center">
                <i class="fas fa-plus-circle mr-1"></i> Novo Virtual Host
            </a>
            <form action="{{ route('virtual-hosts.restart') }}" method="POST" class="restart-form"
                  onsubmit="return confirm('Reiniciar o Apache agora?')">
                @csrf
                <button type="submit"
                   class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-sm inline-flex items-center">
                    <i class="fas fa-redo-alt mr-1"></i> Reiniciar Apache
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded shadow p-5 flex items-center gap-4">
            <div class="bg-indigo-100 rounded-full p-3 text-indigo-600">
                <i class="fas fa-server text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $totalVhosts }}</p>
                <p class="text-xs text-gray-500">Total de Vhosts</p>
            </div>
        </div>
        <div class="bg-white rounded shadow p-5 flex items-center gap-4">
            <div class="bg-green-100 rounded-full p-3 text-green-600">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $activeVhosts }}</p>
                <p class="text-xs text-gray-500">Ativos</p>
            </div>
        </div>
        <div class="bg-white rounded shadow p-5 flex items-center gap-4">
            <div class="bg-blue-100 rounded-full p-3 text-blue-600">
                <i class="fas fa-lock text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $sslCount }}</p>
                <p class="text-xs text-gray-500">Com SSL</p>
            </div>
        </div>
        <div class="bg-white rounded shadow p-5 flex items-center gap-4">
            <div class="{{ $apacheOnline ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }} rounded-full p-3">
                <i class="fas {{ $apacheOnline ? 'fa-check-circle' : 'fa-exclamation-circle' }} text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $apacheOnline ? 'Online' : 'Offline' }}</p>
                <p class="text-xs text-gray-500">Apache</p>
            </div>
        </div>
    </div>

    @if ($sslWithoutCert->isNotEmpty() || $notInApache->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-3"><i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>Alertas</h2>
        <div class="space-y-2">
            @if ($sslWithoutCert->isNotEmpty())
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-certificate"></i>
                    <span>{{ $sslWithoutCert->count() }} vhost(s) com SSL ativado mas sem certificado:</span>
                    @foreach ($sslWithoutCert as $v)
                        <a href="{{ route('virtual-hosts.show', $v) }}" class="underline font-medium">{{ $v->server_name }}</a>@if (!$loop->last), @endif
                    @endforeach
                </div>
            @endif
            @if ($notInApache->isNotEmpty())
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded text-sm flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i>
                    <span>{{ $notInApache->count() }} vhost(s) ativos não encontrados no Apache:</span>
                    @foreach ($notInApache as $v)
                        <a href="{{ route('virtual-hosts.show', $v) }}" class="underline font-medium">{{ $v->server_name }}</a>@if (!$loop->last), @endif
                    @endforeach
                    <a href="{{ route('virtual-hosts.sync') }}" class="ml-auto bg-yellow-200 hover:bg-yellow-300 px-3 py-1 rounded text-xs">Sincronizar</a>
                </div>
            @endif
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded shadow">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800"><i class="fas fa-clock mr-2 text-indigo-500"></i>Últimos Vhosts</h2>
                <a href="{{ route('virtual-hosts.index') }}" class="text-xs text-indigo-600 hover:underline">Ver todos</a>
            </div>
            @if ($recentVhosts->isEmpty())
                <div class="px-5 py-8 text-center text-gray-400 text-sm">
                    <i class="fas fa-globe text-3xl text-gray-300 mb-2 block"></i>
                    Nenhum vhost cadastrado ainda.
                </div>
            @else
                <div class="divide-y">
                    @foreach ($recentVhosts as $v)
                        <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full {{ $v->active ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                <div>
                                    <a href="{{ route('virtual-hosts.show', $v) }}" class="font-medium text-sm text-indigo-600 hover:underline">
                                        {{ $v->server_name }}
                                    </a>
                                    <p class="text-xs text-gray-400">{{ $v->document_root }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-400">
                                @if ($v->template)
                                    <span class="bg-gray-100 px-2 py-0.5 rounded">{{ $v->template }}</span>
                                @endif
                                @if ($v->ssl_enabled)
                                    <i class="fas fa-lock text-green-500" title="SSL"></i>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded shadow">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800"><i class="fas fa-bolt mr-2 text-yellow-500"></i>Ações Rápidas</h2>
            </div>
            <div class="p-5 grid grid-cols-2 gap-3">
                <a href="{{ route('virtual-hosts.create') }}"
                   class="flex items-center gap-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg p-4 text-sm font-medium transition-colors">
                    <i class="fas fa-plus-circle text-lg"></i>
                    <span>Novo Vhost</span>
                </a>
                <a href="{{ route('virtual-hosts.index') }}"
                   class="flex items-center gap-3 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg p-4 text-sm font-medium transition-colors">
                    <i class="fas fa-list text-lg"></i>
                    <span>Listar Vhosts</span>
                </a>
                <a href="{{ route('virtual-hosts.sync') }}"
                   class="flex items-center gap-3 bg-green-50 hover:bg-green-100 text-green-700 rounded-lg p-4 text-sm font-medium transition-colors"
                   onclick="return confirm('Importar todos os hosts do Apache para o banco?')">
                    <i class="fas fa-sync-alt text-lg"></i>
                    <span>Sincronizar</span>
                </a>
                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-3 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg p-4 text-sm font-medium transition-colors">
                    <i class="fas fa-cog text-lg"></i>
                    <span>Configurações</span>
                </a>
                <a href="{{ route('logs.index') }}"
                   class="flex items-center gap-3 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg p-4 text-sm font-medium transition-colors">
                    <i class="fas fa-file-alt text-lg"></i>
                    <span>Logs Apache</span>
                </a>
                @php $phpmyadmin = config('virtualhosts.phpmyadmin_url'); @endphp
                @if ($phpmyadmin)
                    <a href="{{ $phpmyadmin }}" target="_blank"
                       class="flex items-center gap-3 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg p-4 text-sm font-medium transition-colors">
                        <i class="fas fa-database text-lg"></i>
                        <span>phpMyAdmin</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
