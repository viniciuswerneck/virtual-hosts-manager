@extends('layouts.app')

@section('title', 'Gerenciador de Arquivos')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight"><i class="fas fa-folder-open text-blue-500 mr-2"></i>Gerenciador de Arquivos</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Navegue pelos diretórios dos seus projetos</p>
    </div>

    <div class="card p-4 mb-6">
        <form method="GET" action="{{ route('file-manager.index') }}" class="flex items-center gap-3">
            <i class="fas fa-folder text-gray-400"></i>
            <input type="text" name="path" value="{{ $path }}"
                   class="input flex-1 font-mono text-sm">
            <button type="submit" class="btn-primary btn-sm">
                <i class="fas fa-arrow-right"></i> Ir
            </button>
        </form>
    </div>

    <nav class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 mb-4 flex-wrap">
        <a href="{{ route('file-manager.index', ['path' => config('virtualhosts.default_document_root')]) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
            <i class="fas fa-home"></i>
        </a>
        @foreach ($breadcrumbs as $crumb)
            <span class="mx-1 text-gray-300 dark:text-gray-600">/</span>
            <a href="{{ route('file-manager.index', ['path' => $crumb['path']]) }}"
               class="hover:text-blue-600 dark:hover:text-blue-400 {{ $loop->last ? 'text-gray-900 dark:text-gray-100 font-medium' : '' }}">
                {{ $crumb['name'] }}
            </a>
        @endforeach
    </nav>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-gray-800/80 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="text-left px-4 py-3.5">Nome</th>
                        <th class="text-left px-4 py-3.5 hidden sm:table-cell">Tamanho</th>
                        <th class="text-left px-4 py-3.5 hidden md:table-cell">Modificado</th>
                        <th class="text-center px-4 py-3.5">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @if ($path !== '/' && $path !== config('virtualhosts.default_document_root'))
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3" colspan="4">
                                <a href="{{ route('file-manager.index', ['path' => $parent]) }}" class="flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:underline">
                                    <i class="fas fa-level-up-alt"></i> ..
                                </a>
                            </td>
                        </tr>
                    @endif
                    @forelse ($items as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3">
                                @if ($item['type'] === 'dir')
                                    <a href="{{ route('file-manager.index', ['path' => $item['path']]) }}"
                                       class="flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:underline">
                                        <i class="fas fa-folder text-amber-400"></i>
                                        <span class="font-medium">{{ $item['name'] }}</span>
                                    </a>
                                @else
                                    <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                        @php
                                            $icon = match ($item['extension'] ?? '') {
                                                'php' => 'fab fa-php text-blue-400',
                                                'html', 'htm' => 'fab fa-html5 text-orange-500',
                                                'css' => 'fab fa-css3-alt text-blue-500',
                                                'js' => 'fab fa-js text-yellow-500',
                                                'json' => 'fas fa-code text-gray-400',
                                                'md' => 'fab fa-markdown text-gray-400',
                                                'env' => 'fas fa-cog text-gray-400',
                                                'yml', 'yaml' => 'fas fa-cog text-gray-400',
                                                'xml' => 'fas fa-code text-gray-400',
                                                'sql' => 'fas fa-database text-blue-400',
                                                'sh', 'bat' => 'fas fa-terminal text-green-600',
                                                'txt' => 'fas fa-file-alt text-gray-400',
                                                default => 'fas fa-file text-gray-400',
                                            };
                                        @endphp
                                        <i class="{{ $icon }}"></i>
                                        <a href="{{ route('file-manager.show', ['file' => $item['path']]) }}"
                                           class="hover:text-blue-600 dark:hover:text-blue-400">
                                            {{ $item['name'] }}
                                        </a>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                {{ $item['human_size'] ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                {{ $item['modified'] }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($item['type'] === 'dir')
                                    <a href="{{ route('file-manager.index', ['path' => $item['path']]) }}"
                                       class="btn-ghost btn-xs p-1.5" title="Abrir">
                                        <i class="fas fa-folder-open"></i>
                                    </a>
                                    <button onclick="openExplorerPath('{{ str_replace('/', '\\', $item['path']) }}')"
                                            class="btn-ghost btn-xs p-1.5" title="Abrir no Explorer">
                                        <i class="fas fa-external-link-alt"></i>
                                    </button>
                                @else
                                    <a href="{{ route('file-manager.show', ['file' => $item['path']]) }}"
                                       class="btn-ghost btn-xs p-1.5" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 text-gray-400 mb-3 dark:bg-gray-700">
                                    <i class="fas fa-folder-open text-xl"></i>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Diretório vazio.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function openExplorerPath(path) {
            window.open('file:///' + path, '_blank');
        }
    </script>
@endsection
