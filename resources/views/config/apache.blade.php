@extends('layouts.app')

@section('title', __('Configurações do Apache'))

@section('content')
    <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">
                <i class="fas fa-server text-rose-500 mr-2"></i>{{ __('Configurações do Apache') }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                @if ($fileExists)
                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">{{ $filePath }}</code>
                @else
                    <span class="text-amber-600 dark:text-amber-400">{{ __('Arquivo não encontrado:') }} {{ $filePath }}</span>
                @endif
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <form action="{{ route('config.apache.test') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn-secondary btn-sm">
                    <i class="fas fa-check-circle"></i> {{ __('Testar Config') }}
                </button>
            </form>
            <form action="{{ route('config.apache.restart') }}" method="POST" class="restart-form" style="display:inline"
                  onsubmit="return confirm('{{ __("Reiniciar o Apache agora?") }}')">
                @csrf
                <button type="submit" class="btn-warning btn-sm">
                    <i class="fas fa-redo-alt"></i> {{ __('Reiniciar Apache') }}
                </button>
            </form>
        </div>
    </div>

    @if (isset($configTest))
        <div class="mb-5 p-4 rounded-xl {{ $configTest['success'] ? 'bg-emerald-50 border border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/30' : 'bg-red-50 border border-red-200 dark:bg-red-950/20 dark:border-red-800/30' }}">
            <div class="flex items-start gap-3">
                <span class="flex items-center justify-center w-7 h-7 rounded-full shrink-0 {{ $configTest['success'] ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400' : 'bg-red-100 text-red-600 dark:bg-red-400/10 dark:text-red-400' }}">
                    <i class="fas {{ $configTest['success'] ? 'fa-check' : 'fa-times' }} text-xs"></i>
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium {{ $configTest['success'] ? 'text-emerald-800 dark:text-emerald-200' : 'text-red-800 dark:text-red-200' }}">
                        {{ $configTest['success'] ? __('Sintaxe do Apache válida') : __('Erro na configuração') }}
                    </p>
                    @if ($configTest['output'])
                        <pre class="mt-2 text-xs font-mono {{ $configTest['success'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} whitespace-pre-wrap">{{ $configTest['output'] }}</pre>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if (!$fileExists)
        <div class="card p-6 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 mb-4 dark:bg-amber-500/10 dark:text-amber-400">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('Arquivo httpd.conf não encontrado') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                {{ __('O arquivo') }} <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">{{ $filePath }}</code> {{ __('não existe.') }}
            </p>
            <p class="text-xs text-gray-400 dark:text-gray-500">
                {{ __('Configure o caminho correto em:') }} <a href="{{ route('settings.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Configurações do Sistema') }}</a>
            </p>
        </div>
    @else
        @php
            $sections = [];
            foreach ($directives as $key => $dir) {
                $sec = $dir['section'];
                if (!isset($sections[$sec])) $sections[$sec] = [];
                $sections[$sec][$key] = $dir;
            }
        @endphp

        <form action="{{ route('config.apache.update') }}" method="POST">
            @csrf

            @foreach ($sections as $section => $dirs)
                <div class="card p-6 mb-5">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                        <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-400/10 dark:text-rose-400">
                            <i class="fas fa-cog text-xs"></i>
                        </span>
                        {{ __($section) }}
                    </h2>

                    <div class="space-y-4">
                        @foreach ($dirs as $key => $dir)
                            <div>
                                <label class="label" for="apache_{{ $key }}">
                                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded mr-1">{{ $key }}</code>
                                    {{ __($dir['label']) }}
                                </label>
                                <div class="flex gap-2 items-center">
                                    @if ($dir['type'] === 'select')
                                        <select name="directives[{{ $key }}][value]" id="apache_{{ $key }}" class="input flex-1 font-mono text-xs">
                                            @foreach ($dir['options'] as $opt)
                                                <option value="{{ $opt }}" {{ ($dir['value'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="{{ $dir['type'] === 'integer' ? 'number' : 'text' }}"
                                               name="directives[{{ $key }}][value]"
                                               id="apache_{{ $key }}"
                                               value="{{ $dir['value'] ?? $dir['default'] }}"
                                               class="input flex-1 font-mono text-xs"
                                               {{ $dir['type'] === 'integer' ? 'step="1"' : '' }}>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">{{ __($dir['description']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> {{ __('Salvar httpd.conf') }}
                </button>
                <a href="{{ route('dashboard') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('Voltar') }}
                </a>
            </div>
        </form>

        @if (count($modules) > 0)
            <div class="card p-6 mt-6">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-400/10 dark:text-rose-400">
                        <i class="fas fa-puzzle-piece text-xs"></i>
                    </span>
                    {{ __('Módulos do Apache (LoadModule)') }}
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700/30">
                                <th class="text-left py-2 px-2 font-medium">{{ __('Módulo') }}</th>
                                <th class="text-left py-2 px-2 font-medium hidden sm:table-cell">{{ __('Caminho') }}</th>
                                <th class="text-center py-2 px-2 font-medium w-24">{{ __('Status') }}</th>
                                <th class="text-center py-2 px-2 font-medium w-20">{{ __('Ação') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($modules as $mod)
                                <tr class="border-b border-gray-50 dark:border-gray-700/20 hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-2.5 px-2 font-mono text-xs {{ $mod['enabled'] ? 'text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-500' }}">
                                        {{ $mod['name'] }}
                                    </td>
                                    <td class="py-2.5 px-2 text-xs text-gray-400 dark:text-gray-500 hidden sm:table-cell font-mono truncate max-w-xs">
                                        {{ $mod['path'] }}
                                    </td>
                                    <td class="py-2.5 px-2 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $mod['enabled'] ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $mod['enabled'] ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                            {{ $mod['enabled'] ? __('Ativo') : __('Inativo') }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-2 text-center">
                                        <form action="{{ route('config.apache.module-toggle') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="module" value="{{ $mod['name'] }}">
                                            <input type="hidden" name="enable" value="{{ $mod['enabled'] ? '0' : '1' }}">
                                            <button type="submit"
                                                    class="text-xs font-medium {{ $mod['enabled'] ? 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300' : 'text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300' }} transition-colors">
                                                <i class="fas {{ $mod['enabled'] ? 'fa-toggle-on' : 'fa-toggle-off' }} mr-1"></i>
                                                {{ $mod['enabled'] ? __('Desativar') : __('Ativar') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
@endsection
