<!DOCTYPE html>
<html lang="pt-BR" data-theme="classic-blue">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Hosts Manager')) - {{ __('Gerenciador de Virtual Hosts') }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    @if (\Illuminate\Support\Facades\Cache::remember('use_vite', 300, fn() => file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script>
            tailwind.config = { darkMode: 'class' }
        </script>
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script>
        (function() {
            var themes = ['classic-blue','forest-green','royal-purple','sunset-orange','rose-gold','midnight','deep-forest','neon-violet','ember','crimson'];
            var lightThemes = ['classic-blue','forest-green','royal-purple','sunset-orange','rose-gold'];
            var darkThemes = ['midnight','deep-forest','neon-violet','ember','crimson'];
            var saved = localStorage.getItem('theme');

            // Migration: 'light' → 'classic-blue', 'dark' → 'midnight'
            if (saved === 'light') saved = 'classic-blue';
            else if (saved === 'dark') saved = 'midnight';

            if (!saved || !themes.includes(saved)) {
                saved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'midnight' : 'classic-blue';
            }

            var isDark = darkThemes.includes(saved);
            if (isDark) document.documentElement.classList.add('dark');
            document.documentElement.setAttribute('data-theme', saved);
            localStorage.setItem('theme', saved);
        })();
    </script>
    <script>
        (function(){
            var initCollapsed = localStorage.getItem('sidebar_collapsed') === '1';
            var styleEl = document.getElementById('sidebar-dynamic-style');
            if (!styleEl) {
                styleEl = document.createElement('style');
                styleEl.id = 'sidebar-dynamic-style';
                document.head.appendChild(styleEl);
            }
            if (window.innerWidth >= 1024) {
                var sw = initCollapsed ? '4rem' : '16rem';
                var hideLabels = initCollapsed ? '#sidebar .nav-label{width:0;opacity:0}' : '';
                styleEl.textContent = '#sidebar{width:'+sw+'!important}#main-content{margin-left:'+sw+'!important}#topbar{left:'+sw+'!important}'+hideLabels;
            } else {
                styleEl.textContent = '#topbar{left:0!important}';
            }
        })();
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
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.15);
        }
        #sidebar { transition: width .25s cubic-bezier(.16,1,.3,1), transform .25s cubic-bezier(.16,1,.3,1); background: var(--sidebar-bg); border-color: var(--sidebar-border); box-shadow: 0 10px 15px -3px var(--sidebar-shadow), 0 4px 6px -2px rgba(0,0,0,0.03); }
        #sidebar .nav-label { transition: opacity .15s, width .15s; overflow: hidden; white-space: nowrap; }
        #sidebar.collapsed .nav-label { width: 0; opacity: 0; }
        #sidebar.collapsed { width: 4rem; }
        #sidebar:not(.collapsed) { width: 16rem; }
        #sidebar-overlay { transition: opacity .25s ease; }
        #main-content { transition: margin-left .25s cubic-bezier(.16,1,.3,1); }
        @media (max-width: 1023px) {
            #sidebar { position: fixed; left: 0; top: 0; height: 100%; z-index: 50; transform: translateX(-100%); width: 16rem !important; }
            #sidebar.open { transform: translateX(0); }
            #sidebar.collapsed { transform: translateX(-100%); }
            #sidebar.collapsed.open { transform: translateX(0); }
            #main-content { margin-left: 0 !important; }
        }
        @media (min-width: 1024px) {
            #sidebar { transform: translateX(0) !important; }
        }
        .sidebar-header { padding-left: 0.625rem; padding-right: 0.625rem; transition: padding .25s cubic-bezier(.16,1,.3,1); }
        #sidebar-toggle { margin-left: 6px; transition: margin .25s cubic-bezier(.16,1,.3,1); }
        #sidebar.collapsed .sidebar-header { justify-content: center; padding-left: 0; padding-right: 0; }
        #sidebar.collapsed #sidebar-toggle { margin-left: 0; }
        #sidebar.collapsed .sidebar-link { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 0.75rem; border-radius: 0.625rem; font-size: 0.8125rem; font-weight: 500; transition: all 0.15s, padding .25s cubic-bezier(.16,1,.3,1); white-space: nowrap; }
        .sidebar-link:hover { background: var(--nav-hover-bg); color: var(--accent); }
        .sidebar-link-active { background: var(--nav-active-bg); color: var(--nav-active-color); font-weight: 600; }
        .sidebar-icon { display: flex; align-items: center; justify-content: center; width: 1.5rem; height: 1.5rem; flex-shrink: 0; font-size: 0.875rem; }
        #page-loader { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; background: var(--page-bg); transition: opacity .35s ease; }
        #page-loader.hide { opacity: 0; pointer-events: none; }
        .loader-spinner { width: 32px; height: 32px; border: 3px solid var(--loader-border); border-top-color: var(--loader-top); border-radius: 50%; animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Theme selector dropdown */
        .theme-dropdown { position: relative; }
        .theme-menu { display: none; position: absolute; right: 0; top: 100%; margin-top: 0.5rem; background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 0.75rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15); padding: 0.75rem; z-index: 50; min-width: 220px; }
        .theme-menu.show { display: block; }
        .theme-menu-title { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 0.5rem; }
        .theme-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.375rem; margin-bottom: 0.5rem; }
        .theme-option { width: 28px; height: 28px; border-radius: 50%; border: 2px solid transparent; cursor: pointer; transition: all 0.15s; display: flex; align-items: center; justify-content: center; }
        .theme-option:hover { transform: scale(1.15); }
        .theme-option.active { border-color: var(--accent); box-shadow: 0 0 0 2px rgba(var(--accent-rgb), 0.3); }
    </style>
</head>
<body class="min-h-screen antialiased" style="background-color: var(--page-bg);">

    <div id="page-loader"><div class="loader-spinner"></div></div>

    @php
        $apacheOnline = \Illuminate\Support\Facades\Cache::remember('apache_running', 60, function () {
            try { return app(\App\Services\ApacheService::class)->isRunning(); } catch (\Throwable) { return false; }
        });

        $navItems = [
            ['route' => 'dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => __('Dashboard'), 'pattern' => 'dashboard'],
            ['route' => 'virtual-hosts.index', 'icon' => 'fas fa-list', 'label' => __('Virtual Hosts'), 'pattern' => 'virtual-hosts.index', 'exclude' => 'virtual-hosts.create'],
            ['route' => 'virtual-hosts.create', 'icon' => 'fas fa-plus-circle', 'label' => __('Novo'), 'pattern' => 'virtual-hosts.create'],
            ['route' => 'file-manager.index', 'icon' => 'fas fa-folder-open', 'label' => __('Arquivos'), 'pattern' => 'file-manager.*'],
            ['route' => 'activity-logs.index', 'icon' => 'fas fa-history', 'label' => __('Auditoria'), 'pattern' => 'activity-logs.*'],
            ['route' => 'logs.index', 'icon' => 'fas fa-file-alt', 'label' => __('Logs'), 'pattern' => 'logs.*'],
            ['route' => 'databases.index', 'icon' => 'fas fa-database', 'label' => __('Banco de Dados'), 'pattern' => 'databases.*'],
            ['route' => 'config.php.index', 'icon' => 'fab fa-php', 'label' => __('PHP'), 'pattern' => 'config.php.*'],
            ['route' => 'config.apache.index', 'icon' => 'fas fa-server', 'label' => __('Apache'), 'pattern' => 'config.apache.*'],
            ['route' => 'settings.index', 'icon' => 'fas fa-cog', 'label' => __('Sistema'), 'pattern' => 'settings.*'],
        ];

        if (!function_exists('isNavActive')) {
            function isNavActive($item) {
                if (isset($item['exclude']) && request()->routeIs($item['exclude'])) return false;
                return request()->routeIs($item['pattern']);
            }
        }
    @endphp

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <aside id="sidebar"
           class="fixed left-0 top-0 h-full z-50 flex flex-col backdrop-blur-xl border-r">

        <div class="sidebar-header flex items-center h-14 border-b border-gray-100 dark:border-gray-700/30 shrink-0">
            <button id="sidebar-toggle" class="flex items-center justify-center w-9 h-9 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 transition-colors" title="{{ __('Alternar menu') }}">
                <i class="fas fa-bars text-sm"></i>
            </button>
            <a href="{{ route('dashboard') }}" class="nav-label flex items-center gap-2 ml-2 font-semibold text-sm text-gray-900 dark:text-gray-100 overflow-hidden">
                <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-sm shrink-0">
                    <i class="fas fa-server text-xs"></i>
                </span>
                {{ __('Hosts Manager') }}
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-2.5 py-3 space-y-0.5">
            @foreach ($navItems as $item)
                @php $active = isNavActive($item); @endphp
                <a href="{{ route($item['route']) }}"
                   class="sidebar-link {{ $active ? 'sidebar-link-active' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100' }}">
                    <span class="sidebar-icon"><i class="{{ $item['icon'] }}"></i></span>
                    <span class="nav-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>


    </aside>

    <header id="topbar" class="fixed top-0 right-0 left-0 h-14 flex items-center justify-end gap-1 px-4 sm:px-6 border-b border-gray-200/60 dark:border-gray-700/30 backdrop-blur-md z-40" style="background-color: var(--mica-bg);">
        <form action="{{ route('locale.set') }}" method="POST" class="flex items-center gap-1">
            @csrf
            @php $currentLocale = app()->getLocale(); @endphp
            <button type="submit" name="locale" value="pt" class="px-2 py-1 rounded text-xs font-medium transition-colors {{ $currentLocale === 'pt' ? 'text-blue-600 dark:text-orange-400 bg-blue-50 dark:bg-orange-900/20' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}" title="Português">PT</button>
            <button type="submit" name="locale" value="en" class="px-2 py-1 rounded text-xs font-medium transition-colors {{ $currentLocale === 'en' ? 'text-blue-600 dark:text-orange-400 bg-blue-50 dark:bg-orange-900/20' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}" title="English">EN</button>
            <button type="submit" name="locale" value="es" class="px-2 py-1 rounded text-xs font-medium transition-colors {{ $currentLocale === 'es' ? 'text-blue-600 dark:text-orange-400 bg-blue-50 dark:bg-orange-900/20' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}" title="Español">ES</button>
        </form>

        <div class="w-px h-5 bg-gray-200 dark:bg-gray-700"></div>

        <div class="theme-dropdown">
            <button onclick="toggleThemeMenu()" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="{{ __('Alternar tema') }}">
                <svg id="sun-icon-topbar" class="w-3.5 h-3.5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg id="moon-icon-topbar" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <span>{{ __('Tema') }}</span>
            </button>
            <div id="theme-menu" class="theme-menu">
                <div class="theme-menu-title">{{ __('Claro') }}</div>
                <div class="theme-grid" id="light-themes"></div>
                <div class="theme-menu-title">{{ __('Escuro') }}</div>
                <div class="theme-grid" id="dark-themes"></div>
            </div>
        </div>

        <div class="flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
            <i class="fas fa-server text-[10px]"></i>
            <span>{{ __('Apache') }}</span>
            <span class="w-1.5 h-1.5 rounded-full {{ $apacheOnline ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
            <span class="text-[10px] {{ $apacheOnline ? 'text-emerald-500' : 'text-red-400' }}">{{ $apacheOnline ? __('Online') : __('Offline') }}</span>
        </div>

        @if (!empty(config('app.admin_password')))
            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <i class="fas fa-sign-out-alt text-[10px]"></i>
                    <span>{{ __('Sair') }}</span>
                </button>
            </form>
        @endif
    </header>

    <main id="main-content" class="min-h-screen pt-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 lg:py-8">

            <div id="flash-container" class="fixed top-4 right-4 z-30 flex flex-col gap-2 max-w-sm"></div>

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
                        <button type="button" onclick="this.parentElement.remove()" class="shrink-0 opacity-50 hover:opacity-100 transition-opacity {{ $type === 'success' ? 'text-emerald-700 dark:text-emerald-300' : ($type === 'warning' ? 'text-amber-700 dark:text-amber-300' : 'text-red-700 dark:text-red-300') }}" aria-label="{{ __('Fechar') }}">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                @endif
            @endforeach

            @yield('content')

            <footer class="mt-12 pt-6 border-t border-gray-200/60 dark:border-gray-700/30 text-center text-xs text-gray-400 dark:text-gray-500">
                <p>
                    {{ __('Desenvolvido por') }}
                    <a href="https://lab.werneck.dev.br/" class="text-blue-600 dark:text-blue-400 hover:underline font-medium" target="_blank" rel="noopener noreferrer">Werneck Lab</a>
                    &mdash; Hosts Manager v{{ config('app.version', '2.0') }}
                </p>
            </footer>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const isOpen = sidebar.classList.toggle('open');
            const isCollapsed = sidebar.classList.contains('collapsed');

            if (window.innerWidth < 1024) {
                overlay.classList.toggle('hidden', !isOpen);
                document.body.style.overflow = isOpen ? 'hidden' : '';
            }
        }

        function setContentWidth(sw) {
            document.getElementById('main-content').style.setProperty('margin-left', sw, 'important');
            document.getElementById('topbar').style.setProperty('left', sw, 'important');
        }

        function toggleSidebarCollapse() {
            const sidebar = document.getElementById('sidebar');
            const isCollapsed = sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebar_collapsed', isCollapsed ? '1' : '0');
            const styleEl = document.getElementById('sidebar-dynamic-style');
            if (window.innerWidth >= 1024) {
                setContentWidth(isCollapsed ? '4rem' : '16rem');
                // Update hide label style based on collapse state
                if (styleEl) {
                    var base = '#sidebar{width:'+ (isCollapsed ? '4rem' : '16rem') +'!important}#main-content{margin-left:'+(isCollapsed ? '4rem' : '16rem')+'!important}#topbar{left:'+(isCollapsed ? '4rem' : '16rem')+'!important}';
                    var hideLabels = isCollapsed ? '#sidebar .nav-label{width:0;opacity:0}' : '';
                    styleEl.textContent = base + hideLabels;
                }
            }
        }

        function initSidebar() {
            const sidebar = document.getElementById('sidebar');
            const isCollapsed = localStorage.getItem('sidebar_collapsed') === '1';

            if (window.innerWidth >= 1024) {
                var sw = isCollapsed ? '4rem' : '16rem';
                if (isCollapsed) sidebar.classList.add('collapsed');
                setContentWidth(sw);
            } else {
                document.getElementById('topbar').style.setProperty('left', '0', 'important');
            }

            document.getElementById('sidebar-toggle').addEventListener('click', function (e) {
                e.preventDefault();
                if (window.innerWidth < 1024) {
                    toggleSidebar();
                } else {
                    toggleSidebarCollapse();
                }
            });
        }

        var themeDefs = [
            { slug: 'classic-blue', color: '#2563eb', label: 'Classic Blue' },
            { slug: 'forest-green', color: '#059669', label: 'Forest Green' },
            { slug: 'royal-purple', color: '#7c3aed', label: 'Royal Purple' },
            { slug: 'sunset-orange', color: '#ea580c', label: 'Sunset Orange' },
            { slug: 'rose-gold', color: '#e11d48', label: 'Rose Gold' },
            { slug: 'midnight', color: '#1a1a2e', label: 'Midnight' },
            { slug: 'deep-forest', color: '#0a1f1a', label: 'Deep Forest' },
            { slug: 'neon-violet', color: '#1a0a2e', label: 'Neon Violet' },
            { slug: 'ember', color: '#1a1008', label: 'Ember' },
            { slug: 'crimson', color: '#1a0a10', label: 'Crimson' }
        ];
        var lightSlugs = ['classic-blue','forest-green','royal-purple','sunset-orange','rose-gold'];
        var darkSlugs = ['midnight','deep-forest','neon-violet','ember','crimson'];

        function initThemeMenu() {
            var lightGrid = document.getElementById('light-themes');
            var darkGrid = document.getElementById('dark-themes');
            if (!lightGrid || !darkGrid) return;

            var current = localStorage.getItem('theme') || 'classic-blue';
            var lightColors = { 'classic-blue':'#2563eb','forest-green':'#059669','royal-purple':'#7c3aed','sunset-orange':'#ea580c','rose-gold':'#e11d48' };
            var darkColors = { 'midnight':'#60a5fa','deep-forest':'#34d399','neon-violet':'#a78bfa','ember':'#fb923c','crimson':'#fb7185' };

            lightSlugs.forEach(function(slug) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'theme-option' + (current === slug ? ' active' : '');
                btn.style.backgroundColor = lightColors[slug];
                btn.title = slug.replace(/-/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
                btn.onclick = function() { setTheme(slug); };
                lightGrid.appendChild(btn);
            });

            darkSlugs.forEach(function(slug) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'theme-option' + (current === slug ? ' active' : '');
                btn.style.backgroundColor = darkColors[slug];
                btn.title = slug.replace(/-/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
                btn.onclick = function() { setTheme(slug); };
                darkGrid.appendChild(btn);
            });
        }

        function setTheme(slug) {
            var isDark = darkSlugs.includes(slug);
            var html = document.documentElement;

            html.classList.toggle('dark', isDark);
            html.setAttribute('data-theme', slug);
            localStorage.setItem('theme', slug);

            document.getElementById('sun-icon-topbar').classList.toggle('hidden', !isDark);
            document.getElementById('moon-icon-topbar').classList.toggle('hidden', isDark);

            document.querySelectorAll('.theme-option').forEach(function(el) {
                el.classList.remove('active');
            });
            var grids = document.querySelectorAll('.theme-grid');
            grids.forEach(function(grid) {
                var btns = grid.querySelectorAll('button');
                btns.forEach(function(btn) {
                    if (btn.title.toLowerCase().replace(/\s+/g, '-') === slug) {
                        btn.classList.add('active');
                    }
                });
            });

            document.getElementById('theme-menu').classList.remove('show');
        }

        function toggleThemeMenu() {
            document.getElementById('theme-menu').classList.toggle('show');
        }

        document.addEventListener('click', function(e) {
            var menu = document.getElementById('theme-menu');
            var btn = e.target.closest('.theme-dropdown');
            if (!btn && menu) menu.classList.remove('show');
        });

        initThemeMenu();
        document.getElementById('sun-icon-topbar').classList.toggle('hidden', !document.documentElement.classList.contains('dark'));
        document.getElementById('moon-icon-topbar').classList.toggle('hidden', document.documentElement.classList.contains('dark'));

        window.addEventListener('load', function () {
            document.getElementById('page-loader').classList.add('hide');
        });

        document.addEventListener('DOMContentLoaded', function () {
            initSidebar();

            document.querySelectorAll('[role="alert"]').forEach(function (el) {
                setTimeout(function () {
                    el.classList.remove('toast-enter');
                    el.classList.add('toast-leave');
                    setTimeout(function () { el.remove(); }, 300);
                }, 5000);
            });

            document.querySelectorAll('.restart-form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    var btn = this.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-pulse mr-1"></i> {{ __('Reiniciando...') }}';
                    }
                });
            });
        });

        window.addEventListener('resize', function () {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('open');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
                setContentWidth(sidebar.classList.contains('collapsed') ? '4rem' : '16rem');
            } else {
                setContentWidth('0');
            }
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
    </script>
</body>
</html>
