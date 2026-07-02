@extends('layouts.app')

@section('title', 'Configurações do PHP')

@section('content')
    <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">
                <i class="fab fa-php text-indigo-500 mr-2"></i>Configurações do PHP
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                @if ($fileExists)
                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">{{ $filePath }}</code>
                @else
                    <span class="text-amber-600 dark:text-amber-400">Arquivo não encontrado: {{ $filePath }}</span>
                @endif
            </p>
        </div>
    </div>

    @if (!$fileExists)
        <div class="card p-6 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 mb-4 dark:bg-amber-500/10 dark:text-amber-400">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Arquivo php.ini não encontrado</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                O arquivo <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">{{ $filePath }}</code> não existe.
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500">
                Configure o caminho correto do php.ini em: <a href="{{ route('settings.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Configurações do Sistema</a>
            </p>
        </div>
    @else
        <form action="{{ route('config.php.update') }}" method="POST">
            @csrf

            @foreach ($sections as $section => $keys)
                <div class="card p-6 mb-5">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-400/10 dark:text-indigo-400">
                            <i class="fas fa-cog text-xs"></i>
                        </span>
                        {{ $section }}
                    </h2>

                    <div class="space-y-4">
                        @foreach ($keys as $key)
                            @php $dir = $directives[$key]; @endphp
                            <div>
                                <label class="label" for="dir_{{ $key }}">
                                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded mr-1">{{ $key }}</code>
                                    {{ $dir['label'] }}
                                </label>
                                <div class="flex gap-2 items-center">
                                    @if ($dir['type'] === 'select')
                                        <select name="directives[{{ $key }}][value]" id="dir_{{ $key }}" class="input flex-1 font-mono text-xs">
                                            @foreach ($dir['options'] as $opt)
                                                <option value="{{ $opt }}" {{ ($dir['value'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="{{ $dir['type'] === 'integer' ? 'number' : 'text' }}"
                                               name="directives[{{ $key }}][value]"
                                               id="dir_{{ $key }}"
                                               value="{{ $dir['value'] ?? $dir['default'] }}"
                                               class="input flex-1 font-mono text-xs"
                                               {{ $dir['type'] === 'integer' ? 'step="1"' : '' }}>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">{{ $dir['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Salvar php.ini
                </button>
                <a href="{{ route('dashboard') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </form>

        @if (count($extensions) > 0)
            <div class="card p-6 mt-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
                        <i class="fas fa-puzzle-piece text-xs"></i>
                    </span>
                    Extensões do PHP
                </h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">
                    Ative ou desative extensões do PHP comentando/descomentando a linha <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">extension=</code> no php.ini.
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                    @foreach ($extensions as $ext)
                        <div class="flex items-center justify-between gap-2 bg-gray-50 dark:bg-gray-800/50 rounded-lg px-3 py-2 border border-gray-100 dark:border-gray-700/30">
                            <span class="text-xs font-mono text-gray-700 dark:text-gray-300 truncate">{{ $ext['name'] }}</span>
                            <form action="{{ route('config.php.extension-toggle') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="extension" value="{{ $ext['name'] }}">
                                <input type="hidden" name="enable" value="{{ $ext['enabled'] ? '0' : '1' }}">
                                <button type="submit"
                                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors duration-200 focus:outline-none {{ $ext['enabled'] ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"
                                        title="{{ $ext['enabled'] ? 'Desativar' : 'Ativar' }}">
                                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow-sm transition-transform duration-200 {{ $ext['enabled'] ? 'translate-x-[18px]' : 'translate-x-[3px]' }}"></span>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
@endsection
