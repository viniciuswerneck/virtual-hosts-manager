<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Hosts Manager') - Gerenciador de Virtual Hosts</title>
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
        .dark body { background-color: #1a202c; }
        .dark, .dark body, .dark p, .dark span, .dark div, .dark td, .dark th, .dark label { color: #e2e8f0; }
        .dark .bg-white { background-color: #2d3748 !important; }
        .dark .bg-gray-50 { background-color: #4a5568 !important; }
        .dark .text-gray-800 { color: #f7fafc !important; }
        .dark .text-gray-600 { color: #cbd5e0 !important; }
        .dark .text-gray-500 { color: #cbd5e0 !important; }
        .dark .text-gray-400 { color: #a0aec0 !important; }
        .dark .text-gray-700 { color: #e2e8f0 !important; }
        .dark input.bg-white, .dark textarea.bg-white { background-color: #fff !important; }
        .dark input, .dark textarea { color: #1a202c; }
        .dark input::placeholder, .dark textarea::placeholder { color: #718096; }
        .dark .divide-y > * { border-color: #4a5568 !important; }
        .dark .hover\:bg-gray-50:hover { background-color: #4a5568 !important; }
        .dark .hover\:bg-gray-100:hover { background-color: #4a5568 !important; }
        .dark .bg-gray-100 { background-color: #1a202c !important; }
        .dark .bg-gray-200 { background-color: #4a5568 !important; }
        .dark .border { border-color: #4a5568 !important; }
        .dark .border-green-400 { border-color: #48bb78 !important; }
        .dark .text-green-700 { color: #48bb78 !important; }
        .dark .bg-green-100 { background-color: #22543d !important; }
        .dark .bg-red-100 { background-color: #742a2a !important; }
        .dark .border-red-400 { border-color: #fc8181 !important; }
        .dark .text-red-700 { color: #fc8181 !important; }
        .dark .text-indigo-600 { color: #a3bffa !important; }
        .dark .text-indigo-600:hover { color: #c3dafe !important; }
        .dark .text-orange-500 { color: #fbd38d !important; }
        .dark .text-orange-500:hover { color: #fefcbf !important; }
        .dark .text-red-600 { color: #fc8181 !important; }
        .dark .text-red-600:hover { color: #feb2b2 !important; }
        .dark .text-green-600 { color: #68d391 !important; }
        .dark .bg-indigo-700 { background-color: #434190 !important; }
        .dark .hover\:bg-indigo-200:hover { color: #c3dafe !important; }
        .dark .hover\:bg-gray-300:hover { background-color: #4a5568 !important; }
        .dark .hover\:bg-indigo-700:hover { background-color: #3730a3 !important; }
        .dark .hover\:bg-orange-600:hover { background-color: #c05621 !important; }
        .dark .hover\:bg-gray-600:hover { background-color: #718096 !important; }
        .dark .dark { color: #e2e8f0; }
        .dark .font-medium, .dark .font-bold { color: #e2e8f0 !important; }
        .dark .shadow { box-shadow: 0 1px 3px 0 rgba(0,0,0,0.3) !important; }
        .dark .text-red-500 { color: #fc8181 !important; }
        .dark .odd\:bg-white:nth-child(odd) { background-color: #2d3748 !important; }
        .dark .even\:bg-gray-50:nth-child(even) { background-color: #4a5568 !important; }
        .dark table thead tr { background-color: #4a5568 !important; }
        .dark table thead th { color: #e2e8f0 !important; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-indigo-700 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('virtual-hosts.index') }}" class="font-bold text-lg"><i class="fas fa-server mr-2"></i>Hosts Manager</a>
                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('virtual-hosts.index') }}" class="hover:text-indigo-200"><i class="fas fa-list mr-1"></i>Listar</a>
                    <a href="{{ route('virtual-hosts.create') }}" class="hover:text-indigo-200"><i class="fas fa-plus-circle mr-1"></i>Novo Host</a>
                    <a href="{{ route('settings.index') }}" class="hover:text-indigo-200"><i class="fas fa-cog mr-1"></i>Config</a>
                    @php
                        $apacheOnline = false;
                        try { $apacheOnline = app(\App\Services\ApacheService::class)->isRunning(); } catch (\Throwable) {}
                    @endphp
                    <span class="flex items-center gap-1 text-xs {{ $apacheOnline ? 'text-green-300' : 'text-red-300' }}" title="Apache {{ $apacheOnline ? 'rodando' : 'parado' }}">
                        <i class="fas {{ $apacheOnline ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                        Apache
                    </span>
                    @if (!empty(config('app.admin_password')))
                        <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="hover:text-indigo-200" title="Sair"><i class="fas fa-sign-out-alt mr-1"></i>Sair</button>
                        </form>
                    @endif
                    <button onclick="toggleTheme()" class="p-1 rounded hover:bg-indigo-600 focus:outline-none" title="Alternar tema">
                        <svg id="sun-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg id="moon-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        @php
            $flashTypes = ['success' => 'green', 'warning' => 'yellow', 'error' => 'red'];
        @endphp
        @foreach ($flashTypes as $type => $color)
            @if (session($type))
                @php $parts = explode('|', session($type), 2); @endphp
                <div class="bg-{{ $color }}-100 border border-{{ $color }}-400 text-{{ $color }}-700 px-4 py-3 rounded mb-4">
                    {{ $parts[0] }}
                    @if (isset($parts[1]))
                        <span class="block mt-1 text-xs opacity-75">{{ $parts[1] }}</span>
                    @endif
                </div>
            @endif
        @endforeach

        @yield('content')
    </main>

    <footer class="border-t border-gray-200 dark:border-gray-700 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-500">
            <span>Desenvolvido por <a href="https://lab.werneck.dev.br/" class="text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium" target="_blank">Werneck Lab</a> &copy; 2024 - {{ date('Y') }}</span>
            <span class="flex items-center gap-2">
                <i class="fas fa-tag"></i> v{{ config('app.version') }}
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <i class="fas fa-server"></i> Hosts Manager
            </span>
        </div>
    </footer>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateIcons(isDark);
        }

        function updateIcons(isDark) {
            document.getElementById('sun-icon').classList.toggle('hidden', !isDark);
            document.getElementById('moon-icon').classList.toggle('hidden', isDark);
        }

        updateIcons(document.documentElement.classList.contains('dark'));

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
