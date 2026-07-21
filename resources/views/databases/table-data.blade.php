@extends('layouts.app')

@section('title', $table . ' — ' . __('Dados'))

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('databases.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400"><i class="fas fa-database"></i> {{ __('Bancos') }}</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="{{ route('databases.tables', ['database' => $database]) }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ $database }}</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $table }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight"><i class="fas fa-list text-blue-500 mr-2"></i>{{ __('Dados') }}</h1>
    </div>

    <div class="flex gap-2 mb-4">
        <a href="{{ route('databases.table-structure', ['database' => $database, 'table' => $table]) }}" class="btn-secondary btn-sm">
            <i class="fas fa-columns"></i> {{ __('Estrutura') }}
        </a>
        <a href="{{ route('databases.export-table', ['database' => $database, 'table' => $table]) }}" class="btn-secondary btn-sm">
            <i class="fas fa-download"></i> {{ __('Exportar') }}
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-gray-800/80 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        @foreach ($headers as $header)
                            <th class="text-left px-4 py-3.5 whitespace-nowrap">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse ($rows as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            @foreach ($row as $cell)
                                <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300 max-w-[200px] truncate" title="{{ $cell }}">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($headers) }}" class="px-4 py-12 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Tabela vazia.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($lastPage > 1)
        <div class="mt-5 flex items-center justify-between">
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Página :current de :total (:count registros)', ['current' => $page, 'total' => $lastPage, 'count' => number_format($total)]) }}</p>
            <div class="flex gap-2">
                @if ($page > 1)
                    <a href="{{ route('databases.table-data', array_merge(request()->query(), ['page' => $page - 1])) }}" class="btn-secondary btn-xs">
                        <i class="fas fa-chevron-left"></i> {{ __('Anterior') }}
                    </a>
                @endif
                @if ($page < $lastPage)
                    <a href="{{ route('databases.table-data', array_merge(request()->query(), ['page' => $page + 1])) }}" class="btn-secondary btn-xs">
                        {{ __('Próxima') }} <i class="fas fa-chevron-right"></i>
                    </a>
                @endif
            </div>
        </div>
    @endif
@endsection
