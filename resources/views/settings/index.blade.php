@extends('layouts.app')

@section('title', 'Configurações')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Configurações do Sistema</h1>
        <p class="text-gray-500 text-sm mt-1">Configure os caminhos dos arquivos usados pelo gerenciador de virtual hosts.</p>
    </div>

    <div class="bg-white rounded shadow p-6 max-w-2xl">
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Arquivo de Configuração do Apache</label>
                <input type="text" name="apache_vhosts_file" value="{{ old('apache_vhosts_file', $config['apache_vhosts_file']) }}"
                       class="w-full border rounded px-3 py-2 text-sm font-mono">
                <p class="text-gray-400 text-xs mt-1">Ex: C:/Apache24/conf/extra/httpd-vhosts.conf</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Binário do Apache (httpd)</label>
                <input type="text" name="apache_bin" value="{{ old('apache_bin', $config['apache_bin']) }}"
                       class="w-full border rounded px-3 py-2 text-sm font-mono">
                <p class="text-gray-400 text-xs mt-1">Ex: C:/Apache24/bin/httpd.exe</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Serviço Apache</label>
                <input type="text" name="apache_service" value="{{ old('apache_service', $config['apache_service']) }}"
                       class="w-full border rounded px-3 py-2 text-sm font-mono">
                <p class="text-gray-400 text-xs mt-1">Ex: Apache2.4</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Arquivo Hosts do Windows</label>
                <input type="text" name="hosts_file" value="{{ old('hosts_file', $config['hosts_file']) }}"
                       class="w-full border rounded px-3 py-2 text-sm font-mono">
                <p class="text-gray-400 text-xs mt-1">Ex: C:/Windows/System32/drivers/etc/hosts</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Binário do mkcert</label>
                <input type="text" name="mkcert_bin" value="{{ old('mkcert_bin', $config['mkcert_bin']) }}"
                       class="w-full border rounded px-3 py-2 text-sm font-mono">
                <p class="text-gray-400 text-xs mt-1">Ex: C:/mkcert/mkcert.exe</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Diretório de Certificados SSL</label>
                <input type="text" name="mkcert_dir" value="{{ old('mkcert_dir', $config['mkcert_dir']) }}"
                       class="w-full border rounded px-3 py-2 text-sm font-mono">
                <p class="text-gray-400 text-xs mt-1">Ex: C:/mkcert</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Diretório Raiz Padrão</label>
                <input type="text" name="default_document_root" value="{{ old('default_document_root', $config['default_document_root']) }}"
                       class="w-full border rounded px-3 py-2 text-sm font-mono">
                <p class="text-gray-400 text-xs mt-1">Usado como valor padrão ao criar novo host. Ex: D:/www/</p>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded text-sm font-medium">
                    Salvar Configurações
                </button>
                <a href="{{ route('virtual-hosts.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm">
                    Voltar
                </a>
            </div>
        </form>
    </div>
@endsection
