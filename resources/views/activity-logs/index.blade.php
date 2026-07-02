@extends('layouts.app')

@section('title', 'Histórico de Atividades')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight"><i class="fas fa-history text-blue-500 mr-2"></i>Histórico de Atividades</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Registro de todas as operações realizadas no sistema</p>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-gray-800/80 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="text-left px-4 py-3.5">Data/Hora</th>
                        <th class="text-left px-4 py-3.5">Ação</th>
                        <th class="text-left px-4 py-3.5">Descrição</th>
                        <th class="text-left px-4 py-3.5 hidden md:table-cell">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-400 text-xs">
                                {{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @php
                                    $actionColors = [
                                        'created' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-400',
                                        'updated' => 'bg-blue-100 text-blue-700 dark:bg-blue-400/10 dark:text-blue-400',
                                        'deleted' => 'bg-red-100 text-red-700 dark:bg-red-400/10 dark:text-red-400',
                                        'toggled' => 'bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-400',
                                        'synced' => 'bg-violet-100 text-violet-700 dark:bg-violet-400/10 dark:text-violet-400',
                                        'restarted' => 'bg-orange-100 text-orange-700 dark:bg-orange-400/10 dark:text-orange-400',
                                        'imported' => 'bg-sky-100 text-sky-700 dark:bg-sky-400/10 dark:text-sky-400',
                                        'batch_activate' => 'bg-teal-100 text-teal-700 dark:bg-teal-400/10 dark:text-teal-400',
                                        'batch_deactivate' => 'bg-gray-100 text-gray-700 dark:bg-gray-400/10 dark:text-gray-300',
                                        'batch_delete' => 'bg-rose-100 text-rose-700 dark:bg-rose-400/10 dark:text-rose-400',
                                        'backup' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-400',
                                        'backup_restore' => 'bg-purple-100 text-purple-700 dark:bg-purple-400/10 dark:text-purple-400',
                                        'cert_regenerated' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-400/10 dark:text-cyan-400',
                                    ];
                                    $color = $actionColors[$log['action']] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-400/10 dark:text-gray-300';
                                @endphp
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $color }}">
                                    {{ $log['action'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $log['description'] }}</td>
                            <td class="px-4 py-3 text-xs text-gray-400 dark:text-gray-500 hidden md:table-cell font-mono">{{ $log['ip'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 text-gray-400 mb-3 dark:bg-gray-700">
                                    <i class="fas fa-history text-xl"></i>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma atividade registrada ainda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $logs->links() }}
    </div>
@endsection
