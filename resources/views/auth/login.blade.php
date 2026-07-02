<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Hosts Manager</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script>
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
        .dark input:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.15); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-[#f3f6fc] dark:bg-[#1a1a2e] p-4">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full bg-purple-500/10 blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-sm">
        <div class="rounded-2xl border border-gray-200/50 bg-white/80 backdrop-blur-2xl shadow-xl dark:border-gray-700/30 dark:bg-gray-800/80 dark:shadow-2xl p-8">
            <div class="text-center mb-8">
                <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-lg mb-4">
                    <i class="fas fa-server text-lg"></i>
                </span>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Hosts Manager</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gerenciador de Virtual Hosts</p>
            </div>

            @if (session('error'))
                <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50/80 backdrop-blur-sm px-4 py-3 mb-6 dark:border-red-800 dark:bg-red-950/50">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600 shrink-0 dark:bg-red-400/10 dark:text-red-400">
                        <i class="fas fa-exclamation-circle text-xs"></i>
                    </span>
                    <div class="text-sm text-red-700 dark:text-red-300">
                        <p>{{ session('error') }}</p>
                        <p class="mt-2 text-xs opacity-75">Para gerar um hash da senha: <code class="bg-red-100 px-1 rounded dark:bg-red-900">php -r "echo password_hash('sua-senha', PASSWORD_BCRYPT);"</code></p>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.login') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="label" for="password"><i class="fas fa-lock mr-1.5 text-gray-400"></i>Senha de Administrador</label>
                    <input type="password" name="password" id="password" required autofocus
                           class="input" placeholder="Digite sua senha">
                </div>
                <button type="submit" class="btn-primary w-full py-2.5">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>
        </div>
        <p class="text-center text-xs text-gray-400 dark:text-gray-500 mt-6">
            Desenvolvido por <a href="https://lab.werneck.dev.br/" class="text-blue-600 dark:text-blue-400 hover:underline font-medium" target="_blank">Werneck Lab</a>
        </p>
    </div>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        }
    </script>
</body>
</html>
