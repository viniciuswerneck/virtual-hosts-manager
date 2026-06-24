<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Hosts Manager</title>
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
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded shadow p-8 w-full max-w-sm">
        <div class="text-center mb-6">
            <i class="fas fa-server text-indigo-600 text-4xl mb-2"></i>
            <h1 class="text-xl font-bold text-gray-800">Hosts Manager</h1>
            <p class="text-gray-500 text-sm">Gerenciador de Virtual Hosts</p>
        </div>

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.login') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-lock mr-1"></i>Senha de Administrador</label>
                <input type="password" name="password" required autofocus
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium">
                <i class="fas fa-sign-in-alt mr-1"></i> Entrar
            </button>
        </form>
    </div>
</body>
</html>
