@extends('layouts.app')

@section('title', __('Banco de Dados'))

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight"><i class="fas fa-database text-blue-500 mr-2"></i>{{ __('Banco de Dados') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Gerenciar bancos de dados MySQL') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('databases.query-form') }}" class="btn-secondary btn-sm">
                <i class="fas fa-terminal"></i> {{ __('Query SQL') }}
            </a>
            @if ($running)
                <form action="{{ route('mysql.restart') }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Reiniciar MySQL?') }}')">
                    @csrf
                    <button type="submit" class="btn-warning btn-sm"><i class="fas fa-sync"></i> {{ __('Reiniciar MySQL') }}</button>
                </form>
            @endif
        </div>
    </div>

    <!-- MySQL Status -->
    <div class="card p-4 mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg {{ $running ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' }}">
                    <i class="fas fa-database"></i>
                </div>
                <div>
                    <p class="font-medium text-sm text-gray-700 dark:text-gray-300">MySQL Service</p>
                    <p class="text-xs {{ $running ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">{{ $running ? __('Online') : __('Offline') }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                @if (! $running)
                    <form action="{{ route('mysql.start') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn-primary btn-sm"><i class="fas fa-play"></i> {{ __('Iniciar') }}</button>
                    </form>
                @else
                    <form action="{{ route('mysql.stop') }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Parar MySQL? Todas as conexões serão interrompidas.') }}')">
                        @csrf
                        <button type="submit" class="btn-danger btn-sm"><i class="fas fa-stop"></i> {{ __('Parar') }}</button>
                    </form>
                    <form action="{{ route('mysql.restart') }}" method="POST" class="restart-form inline" onsubmit="return confirm('{{ __('Reiniciar MySQL? Todas as conexões serão interrompidas.') }}')">
                        @csrf
                        <button type="submit" class="btn-warning btn-sm"><i class="fas fa-sync"></i> {{ __('Reiniciar') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if (! $available)
        <div class="card p-8 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 text-amber-500 mb-3 dark:bg-amber-400/10">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('MySQL não está disponível. Verifique se o mysql está no PATH do sistema.') }}</p>
        </div>
    @else
        <!-- Create Database -->
        <div class="card p-4 mb-4">
            <form action="{{ route('databases.store') }}" method="POST" class="flex gap-3 items-end">
                @csrf
                <div class="flex-1">
                    <label class="label text-xs">{{ __('Novo Banco de Dados') }}</label>
                    <input type="text" name="name" class="input" placeholder="nome_do_banco" required pattern="[a-zA-Z0-9_]+">
                </div>
                <button type="submit" class="btn-primary btn-sm"><i class="fas fa-plus"></i> {{ __('Criar') }}</button>
            </form>
        </div>

        <!-- Database List -->
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 dark:bg-gray-800/80 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <th class="text-left px-4 py-3.5"><i class="fas fa-database mr-1.5"></i>{{ __('Banco de Dados') }}</th>
                            <th class="text-left px-4 py-3.5"><i class="fas fa-hdd mr-1.5"></i>{{ __('Tamanho') }}</th>
                            <th class="text-center px-4 py-3.5"><i class="fas fa-tools mr-1.5"></i>{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse ($databases as $db)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3.5">
                                    <a href="{{ route('databases.tables', ['database' => $db]) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                        <i class="fas fa-database mr-2 text-gray-400"></i>{{ $db }}
                                    </a>
                                </td>
                                <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 text-xs">{{ $sizes[$db] ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('databases.tables', ['database' => $db]) }}" class="btn-ghost btn-xs" title="{{ __('Tabelas') }}">
                                            <i class="fas fa-table"></i>
                                        </a>
                                        <a href="{{ route('databases.query-form') }}" class="btn-ghost btn-xs text-blue-600 dark:text-blue-400" title="{{ __('Query') }}">
                                            <i class="fas fa-terminal"></i>
                                        </a>
                                        <a href="{{ route('databases.export', ['database' => $db]) }}" class="btn-ghost btn-xs text-emerald-600 dark:text-emerald-400" title="{{ __('Exportar') }}">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="{{ route('databases.import-form', ['database' => $db]) }}" class="btn-ghost btn-xs text-amber-600 dark:text-amber-400" title="{{ __('Importar') }}">
                                            <i class="fas fa-upload"></i>
                                        </a>
                                        <form action="{{ route('databases.destroy', ['database' => $db]) }}" method="POST" class="inline"
                                              onsubmit="return confirm('{{ __('Deletar o banco :db? TODOS os dados serão perdidos!', ['db' => $db]) }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost btn-xs text-red-600 dark:text-red-400" title="{{ __('Deletar') }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-12 text-center">
                                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 text-gray-400 mb-3 dark:bg-gray-700">
                                        <i class="fas fa-database text-xl"></i>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Nenhum banco de dados encontrado.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
