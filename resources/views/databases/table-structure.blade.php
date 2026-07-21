@extends('layouts.app')

@section('title', $table . ' — ' . __('Estrutura'))

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('databases.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400"><i class="fas fa-database"></i> {{ __('Bancos') }}</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="{{ route('databases.tables', ['database' => $database]) }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ $database }}</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $table }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight"><i class="fas fa-columns text-blue-500 mr-2"></i>{{ __('Estrutura') }}</h1>
    </div>

    <div class="flex gap-2 mb-4">
        <a href="{{ route('databases.table-data', ['database' => $database, 'table' => $table]) }}" class="btn-primary btn-sm">
            <i class="fas fa-list"></i> {{ __('Ver Dados') }} ({{ number_format($rowCount) }})
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
                        <th class="text-left px-4 py-3.5">{{ __('Campo') }}</th>
                        <th class="text-left px-4 py-3.5">{{ __('Tipo') }}</th>
                        <th class="text-left px-4 py-3.5">{{ __('Nulo') }}</th>
                        <th class="text-left px-4 py-3.5">{{ __('Chave') }}</th>
                        <th class="text-left px-4 py-3.5">{{ __('Padrão') }}</th>
                        <th class="text-left px-4 py-3.5">{{ __('Extra') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse ($columns as $col)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-gray-100">{{ $col['field'] }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-blue-600 dark:text-blue-400">{{ $col['type'] }}</td>
                            <td class="px-4 py-3 text-xs">{{ $col['null'] }}</td>
                            <td class="px-4 py-3">
                                @if ($col['key'] === 'PRI')
                                    <span class="badge-green text-xs"><i class="fas fa-key"></i> PRI</span>
                                @elseif ($col['key'] === 'UNI')
                                    <span class="badge-blue text-xs"><i class="fas fa-key"></i> UNI</span>
                                @elseif ($col['key'] === 'MUL')
                                    <span class="badge-gray text-xs"><i class="fas fa-key"></i> MUL</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $col['default'] ?? 'NULL' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $col['extra'] ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Nenhuma coluna encontrada.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
