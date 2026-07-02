<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Hosts Manager') - Gerenciador de Virtual Hosts</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script>
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        .toast-enter { animation: slideIn .35s cubic-bezier(.16,1,.3,1); }
        .toast-leave { animation: fadeOut .25s ease-in forwards; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; transform: translateX(100%); } }
        input:not([type="checkbox"]):not([type="radio"]):focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .dark input:not([type="checkbox"]):not([type="radio"]):focus,
        .dark textarea:focus,
        .dark select:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.15);
        }
    </style>
</head>
<body class="bg-[#f3f6fc] dark:bg-[#1a1a2e] min-h-screen antialiased">
    <nav class="mica sticky top-0 z-40 border-b border-gray-200/60 dark:border-gray-700/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 font-semibold text-lg text-gray-900 dark:text-gray-100">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-sm">
                        <i class="fas fa-server text-sm"></i>
                    </span>
                    Hosts Manager
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : 'text-gray-600 dark:text-gray-300' }}">
                        <i class="fas fa-tachometer-alt text-sm"></i>
                        <span class="hidden sm:inline">Dashboard</span>
                    </a>
                    <a href="{{ route('virtual-hosts.index') }}"
                       class="nav-link {{ request()->routeIs('virtual-hosts.*') && !request()->routeIs('virtual-hosts.create') ? 'nav-link-active' : 'text-gray-600 dark:text-gray-300' }}">
                        <i class="fas fa-list text-sm"></i>
                        <span class="hidden sm:inline">Virtual Hosts</span>
                    </a>
                    <a href="{{ route('virtual-hosts.create') }}"
                       class="nav-link {{ request()->routeIs('virtual-hosts.create') ? 'nav-link-active' : 'text-gray-600 dark:text-gray-300' }}">
                        <i class="fas fa-plus-circle text-sm"></i>
                        <span class="hidden sm:inline">Novo</span>
                    </a>
                    <a href="{{ route('logs.index') }}"
                       class="nav-link {{ request()->routeIs('logs.*') ? 'nav-link-active' : 'text-gray-600 dark:text-gray-300' }}">
                        <i class="fas fa-file-alt text-sm"></i>
                        <span class="hidden sm:inline">Logs</span>
                    </a>
                    <a href="{{ route('settings.index') }}"
                       class="nav-link {{ request()->routeIs('settings.*') ? 'nav-link-active' : 'text-gray-600 dark:text-gray-300' }}">
                        <i class="fas fa-cog text-sm"></i>
                        <span class="hidden sm:inline">Config</span>
                    </a>
                    <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>
                    @php
                        $apacheOnline = \Illuminate\Support\Facades\Cache::remember('apache_running', 10, function () {
                            try { return app(\App\Services\ApacheService::class)->isRunning(); } catch (\Throwable) { return false; }
                        });
                    @endphp
                    <span class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full {{ $apacheOnline ? 'badge-green' : 'badge-red' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $apacheOnline ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                        Apache
                    </span>
                    @if (!empty(config('app.admin_password')))
                        <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn-ghost btn-xs" title="Sair">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    @endif
                    <button onclick="toggleTheme()" class="btn-ghost btn-xs p-1.5" title="Alternar tema">
                        <svg id="sun-icon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg id="moon-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <div id="flash-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm"></div>

        @php
            $flashTypes = ['success' => ['color' => 'emerald', 'icon' => 'check-circle'], 'warning' => ['color' => 'amber', 'icon' => 'exclamation-triangle'], 'error' => ['color' => 'red', 'icon' => 'times-circle']];
        @endphp
        @foreach ($flashTypes as $type => $cfg)
            @if (session($type))
                @php $parts = explode('|', session($type), 2); @endphp
                <div class="toast-enter flex items-start gap-3 rounded-xl border bg-white/90 backdrop-blur-xl px-4 py-3 shadow-lg ring-1 ring-black/5 dark:bg-gray-800/90 dark:ring-white/10 mb-4 {{ $type === 'success' ? 'border-emerald-200 dark:border-emerald-800' : ($type === 'warning' ? 'border-amber-200 dark:border-amber-800' : 'border-red-200 dark:border-red-800') }}" role="alert">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full {{ $type === 'success' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400' : ($type === 'warning' ? 'bg-amber-100 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400' : 'bg-red-100 text-red-600 dark:bg-red-400/10 dark:text-red-400') }}">
                        <i class="fas fa-{{ $cfg['icon'] }} text-xs"></i>
                    </span>
                    <div class="flex-1 text-sm {{ $type === 'success' ? 'text-emerald-800 dark:text-emerald-200' : ($type === 'warning' ? 'text-amber-800 dark:text-amber-200' : 'text-red-800 dark:text-red-200') }}">
                        {{ $parts[0] }}
                        @if (isset($parts[1]))
                            <span class="block mt-1 text-xs opacity-75">{{ $parts[1] }}</span>
                        @endif
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="shrink-0 opacity-50 hover:opacity-100 transition-opacity {{ $type === 'success' ? 'text-emerald-700 dark:text-emerald-300' : ($type === 'warning' ? 'text-amber-700 dark:text-amber-300' : 'text-red-700 dark:text-red-300') }}" aria-label="Fechar">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            @endif
        @endforeach

        @yield('content')
    </main>

    <footer class="border-t border-gray-200/60 dark:border-gray-700/40 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 flex items-center justify-between text-xs text-gray-500 dark:text-gray-500">
            <span>Desenvolvido por <a href="https://lab.werneck.dev.br/" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium" target="_blank">Werneck Lab</a> &copy; 2024 - {{ date('Y') }}</span>
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1.5">
                    <i class="fab fa-laravel text-red-500"></i>
                    Laravel
                </span>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <span class="flex items-center gap-1.5">
                    <i class="fas fa-tag"></i> v{{ config('app.version') }}
                </span>
            </div>
        </div>
    </footer>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            document.getElementById('sun-icon').classList.toggle('hidden', !isDark);
            document.getElementById('moon-icon').classList.toggle('hidden', isDark);
        }
        function updateIcons(isDark) {
            document.getElementById('sun-icon').classList.toggle('hidden', !isDark);
            document.getElementById('moon-icon').classList.toggle('hidden', isDark);
        }
        updateIcons(document.documentElement.classList.contains('dark'));

        document.querySelectorAll('[role="alert"]').forEach(function (el) {
            setTimeout(function () {
                el.classList.remove('toast-enter');
                el.classList.add('toast-leave');
                setTimeout(function () { el.remove(); }, 300);
            }, 5000);
        });

        function openExplorer() {
            var path = document.getElementById('document_root').value.replace(/\//g, '\\');
            window.open('file:///' + path, '_blank');
        }
        function openInVSCode(path) {
            var vsPath = path.replace(/\//g, '\\');
            window.open('vscode://file/' + vsPath, '_blank');
        }
        function copyToClipboard(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).catch(function () { fallbackCopy(text); });
            } else {
                fallbackCopy(text);
            }
        }
        function fallbackCopy(text) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); } catch (e) {}
            document.body.removeChild(ta);
        }
        document.querySelectorAll('.restart-form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-pulse mr-1"></i> Reiniciando...';
                }
            });
        });
    </script>
</body>
</html>
