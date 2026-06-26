@extends('layouts.app')

@section('title', 'Configurações')

@php
    use Illuminate\Support\Facades\File;

    $defaults = [
        'apache_vhosts_file' => 'C:/Apache24/conf/extra/httpd-vhosts.conf',
        'apache_bin' => 'C:/Apache24/bin/httpd.exe',
        'apache_service' => 'Apache2.4',
        'apache_ssl_port' => '443',
        'apache_error_log' => 'C:/Apache24/logs/error.log',
        'hosts_file' => 'C:/Windows/System32/drivers/etc/hosts',
        'mkcert_bin' => 'C:/mkcert/mkcert.exe',
        'mkcert_dir' => 'C:/mkcert',
        'default_document_root' => 'D:/www/',
        'phpmyadmin_url' => '',
        'vscode_executable' => 'code',
    ];

    $pathChecks = ['apache_vhosts_file', 'apache_bin', 'apache_error_log', 'hosts_file', 'mkcert_bin', 'mkcert_dir', 'default_document_root'];
    $exists = [];
    foreach ($pathChecks as $key) {
        $val = $config[$key] ?? '';
        if ($key === 'mkcert_dir' || $key === 'default_document_root') {
            $exists[$key] = File::isDirectory($val);
        } elseif ($key === 'apache_service' || $key === 'phpmyadmin_url' || $key === 'vscode_executable') {
            $exists[$key] = null;
        } else {
            $exists[$key] = File::exists($val);
        }
    }
@endphp

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><i class="fas fa-cogs text-indigo-600 mr-2"></i>Configurações do Sistema</h1>
        <p class="text-gray-500 text-sm mt-1"><i class="fas fa-info-circle mr-1"></i>Configure os caminhos dos arquivos usados pelo gerenciador de virtual hosts.</p>
    </div>

    <div class="bg-white rounded shadow p-6 max-w-2xl">
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf

            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b"><i class="fas fa-server mr-2 text-indigo-600"></i>Apache</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-file-code mr-1 text-gray-500"></i>Arquivo de Configuração do Apache</label>
                <div class="flex gap-1 items-center">
                    <input type="text" name="apache_vhosts_file" id="apache_vhosts_file" value="{{ old('apache_vhosts_file', $config['apache_vhosts_file']) }}"
                           class="flex-1 border rounded px-3 py-2 text-sm font-mono bg-white">
                    <span class="text-lg">{{ $exists['apache_vhosts_file'] ?? false ? '✅' : ($exists['apache_vhosts_file'] ?? true ? '—' : '❌') }}</span>
                    <button type="button" onclick="restore('apache_vhosts_file', '{{ $defaults['apache_vhosts_file'] }}')" class="text-gray-400 hover:text-gray-600 text-xs" title="Restaurar padrão">↩</button>
                </div>
                <p class="text-gray-400 text-xs mt-1">Ex: C:/Apache24/conf/extra/httpd-vhosts.conf</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-cogs mr-1 text-gray-500"></i>Binário do Apache (httpd)</label>
                <div class="flex gap-1 items-center">
                    <input type="text" name="apache_bin" id="apache_bin" value="{{ old('apache_bin', $config['apache_bin']) }}"
                           class="flex-1 border rounded px-3 py-2 text-sm font-mono bg-white">
                    <span class="text-lg">{{ $exists['apache_bin'] ?? false ? '✅' : ($exists['apache_bin'] ?? true ? '—' : '❌') }}</span>
                    <button type="button" onclick="restore('apache_bin', '{{ $defaults['apache_bin'] }}')" class="text-gray-400 hover:text-gray-600 text-xs" title="Restaurar padrão">↩</button>
                </div>
                <p class="text-gray-400 text-xs mt-1">Ex: C:/Apache24/bin/httpd.exe</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-server mr-1 text-gray-500"></i>Nome do Serviço Apache</label>
                <div class="flex gap-1 items-center">
                    <input type="text" name="apache_service" id="apache_service" value="{{ old('apache_service', $config['apache_service']) }}"
                           class="flex-1 border rounded px-3 py-2 text-sm font-mono bg-white">
                    <span class="text-gray-300 text-lg" title="Não é um caminho de arquivo">—</span>
                    <button type="button" onclick="restore('apache_service', '{{ $defaults['apache_service'] }}')" class="text-gray-400 hover:text-gray-600 text-xs" title="Restaurar padrão">↩</button>
                </div>
                <p class="text-gray-400 text-xs mt-1">Ex: Apache2.4</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-lock mr-1 text-gray-500"></i>Porta SSL do Apache</label>
                <div class="flex gap-1 items-center">
                    <input type="number" name="apache_ssl_port" id="apache_ssl_port" value="{{ old('apache_ssl_port', $config['apache_ssl_port']) }}"
                           class="flex-1 border rounded px-3 py-2 text-sm font-mono bg-white">
                    <span class="text-gray-300 text-lg">—</span>
                    <button type="button" onclick="restore('apache_ssl_port', '{{ $defaults['apache_ssl_port'] }}')" class="text-gray-400 hover:text-gray-600 text-xs" title="Restaurar padrão">↩</button>
                </div>
                <p class="text-gray-400 text-xs mt-1">Porta padrão para VirtualHosts SSL (padrão: 443)</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-file-alt mr-1 text-gray-500"></i>Arquivo de Log de Erros do Apache</label>
                <div class="flex gap-1 items-center">
                    <input type="text" name="apache_error_log" id="apache_error_log" value="{{ old('apache_error_log', $config['apache_error_log']) }}"
                           class="flex-1 border rounded px-3 py-2 text-sm font-mono bg-white">
                    <span class="text-lg">{{ $exists['apache_error_log'] ?? false ? '✅' : ($exists['apache_error_log'] ?? true ? '—' : '❌') }}</span>
                    <button type="button" onclick="restore('apache_error_log', '{{ $defaults['apache_error_log'] }}')" class="text-gray-400 hover:text-gray-600 text-xs" title="Restaurar padrão">↩</button>
                </div>
                <p class="text-gray-400 text-xs mt-1">Usado pelo visualizador de logs. Ex: C:/Apache24/logs/error.log</p>
            </div>

            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b mt-8"><i class="fas fa-shield-alt mr-2 text-indigo-600"></i>SSL / Certificados</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-shield-alt mr-1 text-gray-500"></i>Binário do mkcert</label>
                <div class="flex gap-1 items-center">
                    <input type="text" name="mkcert_bin" id="mkcert_bin" value="{{ old('mkcert_bin', $config['mkcert_bin']) }}"
                           class="flex-1 border rounded px-3 py-2 text-sm font-mono bg-white">
                    <span class="text-lg">{{ $exists['mkcert_bin'] ?? false ? '✅' : ($exists['mkcert_bin'] ?? true ? '—' : '❌') }}</span>
                    <button type="button" onclick="restore('mkcert_bin', '{{ $defaults['mkcert_bin'] }}')" class="text-gray-400 hover:text-gray-600 text-xs" title="Restaurar padrão">↩</button>
                </div>
                <p class="text-gray-400 text-xs mt-1">Ex: C:/mkcert/mkcert.exe</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-folder-open mr-1 text-gray-500"></i>Diretório de Certificados SSL</label>
                <div class="flex gap-1 items-center">
                    <input type="text" name="mkcert_dir" id="mkcert_dir" value="{{ old('mkcert_dir', $config['mkcert_dir']) }}"
                           class="flex-1 border rounded px-3 py-2 text-sm font-mono bg-white">
                    <span class="text-lg">{{ $exists['mkcert_dir'] ?? false ? '✅' : ($exists['mkcert_dir'] ?? true ? '—' : '❌') }}</span>
                    <button type="button" onclick="restore('mkcert_dir', '{{ $defaults['mkcert_dir'] }}')" class="text-gray-400 hover:text-gray-600 text-xs" title="Restaurar padrão">↩</button>
                </div>
                <p class="text-gray-400 text-xs mt-1">Ex: C:/mkcert</p>
            </div>

            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b mt-8"><i class="fas fa-folder mr-2 text-indigo-600"></i>Paths</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-network-wired mr-1 text-gray-500"></i>Arquivo Hosts do Windows</label>
                <div class="flex gap-1 items-center">
                    <input type="text" name="hosts_file" id="hosts_file" value="{{ old('hosts_file', $config['hosts_file']) }}"
                           class="flex-1 border rounded px-3 py-2 text-sm font-mono bg-white">
                    <span class="text-lg">{{ $exists['hosts_file'] ?? false ? '✅' : ($exists['hosts_file'] ?? true ? '—' : '❌') }}</span>
                    <button type="button" onclick="restore('hosts_file', '{{ $defaults['hosts_file'] }}')" class="text-gray-400 hover:text-gray-600 text-xs" title="Restaurar padrão">↩</button>
                </div>
                <p class="text-gray-400 text-xs mt-1">Ex: C:/Windows/System32/drivers/etc/hosts</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-folder mr-1 text-gray-500"></i>Diretório Raiz Padrão</label>
                <div class="flex gap-1 items-center">
                    <input type="text" name="default_document_root" id="default_document_root" value="{{ old('default_document_root', $config['default_document_root']) }}"
                           class="flex-1 border rounded px-3 py-2 text-sm font-mono bg-white">
                    <span class="text-lg">{{ $exists['default_document_root'] ?? false ? '✅' : ($exists['default_document_root'] ?? true ? '—' : '❌') }}</span>
                    <button type="button" onclick="restore('default_document_root', '{{ $defaults['default_document_root'] }}')" class="text-gray-400 hover:text-gray-600 text-xs" title="Restaurar padrão">↩</button>
                </div>
                <p class="text-gray-400 text-xs mt-1">Usado como valor padrão ao criar novo host. Ex: D:/www/</p>
            </div>

            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b mt-8"><i class="fas fa-link mr-2 text-indigo-600"></i>Integrações</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-database mr-1 text-gray-500"></i>URL do phpMyAdmin</label>
                <div class="flex gap-1 items-center">
                    <input type="text" name="phpmyadmin_url" id="phpmyadmin_url" value="{{ old('phpmyadmin_url', $config['phpmyadmin_url']) }}"
                           class="flex-1 border rounded px-3 py-2 text-sm font-mono bg-white"
                           placeholder="http://localhost/phpmyadmin">
                    <span class="text-gray-300 text-lg">—</span>
                    <button type="button" onclick="restore('phpmyadmin_url', '')" class="text-gray-400 hover:text-gray-600 text-xs" title="Limpar">↩</button>
                </div>
                <p class="text-gray-400 text-xs mt-1">Usado para atalho rápido no dashboard. Ex: http://localhost/phpmyadmin</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-code mr-1 text-gray-500"></i>Comando VS Code</label>
                <div class="flex gap-1 items-center">
                    <input type="text" name="vscode_executable" id="vscode_executable" value="{{ old('vscode_executable', $config['vscode_executable']) }}"
                           class="flex-1 border rounded px-3 py-2 text-sm font-mono bg-white">
                    <span class="text-gray-300 text-lg">—</span>
                    <button type="button" onclick="restore('vscode_executable', 'code')" class="text-gray-400 hover:text-gray-600 text-xs" title="Restaurar padrão">↩</button>
                </div>
                <p class="text-gray-400 text-xs mt-1">Comando para abrir o VS Code. Padrão: <code>code</code></p>
            </div>

            <div class="mb-6 p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-700">
                <p><strong>💡 Certificados SSL:</strong> Se o navegador não confiar nos certificados, execute no PowerShell como Administrador:</p>
                <code class="block mt-1 bg-blue-100 px-2 py-1 rounded">$env:CAROOT="D:\www\localserver\storage\app\mkcert"; mkcert -install</code>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded text-sm font-medium">
                    <i class="fas fa-save mr-1"></i> Salvar Configurações
                </button>
                <a href="{{ route('virtual-hosts.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </a>
            </div>
        </form>
    </div>

    <script>
        function restore(fieldId, defaultValue) {
            document.getElementById(fieldId).value = defaultValue;
        }
    </script>
@endsection
