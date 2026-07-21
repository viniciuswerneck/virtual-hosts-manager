@extends('layouts.app')

@section('title', __('Query SQL'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight"><i class="fas fa-terminal text-blue-500 mr-2"></i>{{ __('Query SQL') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Execute queries SQL no MySQL') }}</p>
    </div>

    <div class="card p-6">
        <form action="{{ isset($database) ? route('databases.query', ['database' => $database]) : route('databases.query-form') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="label" for="database"><i class="fas fa-database mr-1.5 text-gray-400"></i>{{ __('Banco de Dados') }}</label>
                    <select name="database" id="database" class="input" required>
                        <option value="">{{ __('Selecione um banco') }}</option>
                        @foreach ($databases as $db)
                            <option value="{{ $db }}" {{ ($database ?? '') === $db ? 'selected' : '' }}>{{ $db }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label" for="sql"><i class="fas fa-code mr-1.5 text-gray-400"></i>{{ __('SQL') }}</label>
                    <textarea name="sql" id="sql" rows="8" class="input font-mono text-xs"
                              placeholder="SELECT * FROM tabela WHERE coluna = 'valor'"
                              required>{{ old('sql', $sql ?? '') }}</textarea>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-play"></i> {{ __('Executar') }}
                    </button>
                    <button type="button" onclick="document.getElementById('sql').value=''" class="btn-ghost">
                        <i class="fas fa-times"></i> {{ __('Limpar') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if (isset($success))
        <div class="card p-4 mt-4">
            @if (isset($rows) && count($rows) > 0)
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
                            @foreach ($rows as $row)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                    @foreach ($row as $cell)
                                        <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300 max-w-[200px] truncate" title="{{ $cell }}">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-400 mt-3">{{ __('Resultado: :count linha(s)', ['count' => count($rows)]) }}</p>
            @else
                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                    <i class="fas fa-check-circle"></i>
                    <span class="text-sm">{{ __('Query executada com sucesso.') }}</span>
                </div>
            @endif
        </div>
    @endif

    @if (isset($error) && $error)
        <div class="card p-4 mt-4 border-red-200 dark:border-red-800">
            <div class="flex items-start gap-2 text-red-600 dark:text-red-400">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <div>
                    <p class="text-sm font-medium">{{ __('Erro na query') }}</p>
                    <pre class="text-xs mt-2 font-mono whitespace-pre-wrap">{{ $error }}</pre>
                </div>
            </div>
        </div>
    @endif
@endsection
