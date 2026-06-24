<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Hosts Manager</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
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
        .dark body { background-color: #1a202c; }
        .dark .bg-white { background-color: #2d3748 !important; }
        .dark, .dark body, .dark h1, .dark p, .dark label { color: #e2e8f0; }
        .dark .text-gray-800 { color: #f7fafc !important; }
        .dark .text-gray-500 { color: #cbd5e0 !important; }
        .dark .text-gray-700 { color: #e2e8f0 !important; }
        .dark .text-indigo-600 { color: #a3bffa !important; }
        .dark .bg-indigo-600 { background-color: #434190 !important; }
        .dark .hover\:bg-indigo-700:hover { background-color: #3730a3 !important; }
        .dark .bg-red-100 { background-color: #742a2a !important; }
        .dark .border-red-400 { border-color: #fc8181 !important; }
        .dark .text-red-700 { color: #fc8181 !important; }
        .dark .bg-red-200 { background-color: #9b2c2c !important; }
        input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center dark:bg-gray-900">
    <div class="bg-white rounded shadow p-8 w-full max-w-sm dark:bg-gray-800 dark:shadow-lg">
        <div class="text-center mb-6">
            <i class="fas fa-server text-indigo-600 text-4xl mb-2 dark:text-indigo-400"></i>
            <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">Hosts Manager</h1>
            <p class="text-gray-500 text-sm dark:text-gray-400">Gerenciador de Virtual Hosts</p>
        </div>

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm dark:bg-red-900 dark:border-red-500 dark:text-red-200">
                <p>{{ session('error') }}</p>
                <p class="mt-2 text-xs opacity-75">Para gerar um hash da senha: <code class="bg-red-200 px-1 rounded dark:bg-red-800">php -r "echo password_hash('sua-senha', PASSWORD_BCRYPT);"</code></p>
            </div>
        @endif

        <form action="{{ route('admin.login') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300"><i class="fas fa-lock mr-1"></i>Senha de Administrador</label>
                <input type="password" name="password" required autofocus
                       class="w-full border rounded px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium dark:bg-indigo-700 dark:hover:bg-indigo-800">
                <i class="fas fa-sign-in-alt mr-1"></i> Entrar
            </button>
        </form>
    </div>
</body>
</html>
