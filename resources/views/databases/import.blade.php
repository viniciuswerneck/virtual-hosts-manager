@extends('layouts.app')

@section('title', __('Importar SQL') . ' — ' . $database)

@section('content')
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
            <a href="{{ route('databases.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400"><i class="fas fa-database"></i> {{ __('Bancos') }}</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="{{ route('databases.tables', ['database' => $database]) }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ $database }}</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900 dark:text-gray-100 font-medium">{{ __('Importar') }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight"><i class="fas fa-upload text-blue-500 mr-2"></i>{{ __('Importar SQL') }}</h1>
    </div>

    <div class="card p-6 max-w-xl">
        <form action="{{ route('databases.import', ['database' => $database]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="label" for="sql_file"><i class="fas fa-file-code mr-1.5 text-gray-400"></i>{{ __('Arquivo SQL') }}</label>
                    <input type="file" name="sql_file" id="sql_file" accept=".sql,.txt"
                           class="input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-400/10 dark:file:text-blue-400 hover:file:bg-blue-100"
                           required>
                    <p class="text-xs text-gray-400 mt-1.5">{{ __('Aceita arquivos .sql ou .txt') }}</p>
                </div>

                <div class="bg-amber-50 dark:bg-amber-400/10 border border-amber-200 dark:border-amber-800 rounded-lg p-3 text-sm text-amber-700 dark:text-amber-400">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    {{ __('O conteúdo do arquivo será executado no banco :db. Certifique-se de que o SQL é seguro.', ['db' => $database]) }}
                </div>
            </div>

            <div class="flex gap-2 mt-6">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-upload"></i> {{ __('Importar') }}
                </button>
                <a href="{{ route('databases.tables', ['database' => $database]) }}" class="btn-ghost">
                    {{ __('Cancelar') }}
                </a>
            </div>
        </form>
    </div>
@endsection
