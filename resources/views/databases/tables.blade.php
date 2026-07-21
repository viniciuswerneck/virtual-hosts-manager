@extends('layouts.app')

@section('title', __('Tabelas') . ' — ' . $database)

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('databases.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400"><i class="fas fa-database"></i> {{ __('Bancos') }}</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $database }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight"><i class="fas fa-table text-blue-500 mr-2"></i>{{ __('Tabelas') }}</h1>
    </div>

    <div class="flex gap-2 mb-4">
        <a href="{{ route('databases.export', ['database' => $database]) }}" class="btn-secondary btn-sm">
            <i class="fas fa-download"></i> {{ __('Exportar Banco') }}
        </a>
        <a href="{{ route('databases.import-form', ['database' => $database]) }}" class="btn-secondary btn-sm">
            <i class="fas fa-upload"></i> {{ __('Importar SQL') }}
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-gray-800/80 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="text-left px-4 py-3.5"><i class="fas fa-table mr-1.5"></i>{{ __('Tabela') }}</th>
                        <th class="text-right px-4 py-3.5"><i class="fas fa-hashtag mr-1.5"></i>{{ __('Registros') }}</th>
                        <th class="text-center px-4 py-3.5"><i class="fas fa-tools mr-1.5"></i>{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse ($tables as $table)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3.5">
                                <a href="{{ route('databases.table-data', ['database' => $database, 'table' => $table]) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                    <i class="fas fa-table mr-2 text-gray-400"></i>{{ $table }}
                                </a>
                            </td>
                            <td class="px-4 py-3.5 text-right text-gray-500 dark:text-gray-400 font-mono text-xs">{{ number_format($counts[$table] ?? 0) }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('databases.table-structure', ['database' => $database, 'table' => $table]) }}" class="btn-ghost btn-xs" title="{{ __('Estrutura') }}">
                                        <i class="fas fa-columns"></i>
                                    </a>
                                    <a href="{{ route('databases.table-data', ['database' => $database, 'table' => $table]) }}" class="btn-ghost btn-xs text-blue-600 dark:text-blue-400" title="{{ __('Dados') }}">
                                        <i class="fas fa-list"></i>
                                    </a>
                                    <a href="{{ route('databases.export-table', ['database' => $database, 'table' => $table]) }}" class="btn-ghost btn-xs text-emerald-600 dark:text-emerald-400" title="{{ __('Exportar') }}">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <form action="{{ route('databases.truncate-table', ['database' => $database, 'table' => $table]) }}" method="POST" class="inline"
                                          onsubmit="return confirm('{{ __('Truncar a tabela :table? Todos os dados serão perdidos!', ['table' => $table]) }}')">
                                        @csrf
                                        <button type="submit" class="btn-ghost btn-xs text-amber-600 dark:text-amber-400" title="{{ __('Truncar') }}">
                                            <i class="fas fa-eraser"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('databases.drop-table', ['database' => $database, 'table' => $table]) }}" method="POST" class="inline"
                                          onsubmit="return confirm('{{ __('Deletar a tabela :table? Isso é irreversível!', ['table' => $table]) }}')">
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
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Nenhuma tabela encontrada.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
