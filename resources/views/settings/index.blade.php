@extends('layouts.app')

@section('title', __('Configurações'))

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
        'phpmyadmin_user' => 'root',
        'phpmyadmin_password' => '',
        'vscode_executable' => 'code',
        'php_ini_file' => 'C:/php/php.ini',
        'httpd_conf_file' => 'C:/Apache24/conf/httpd.conf',
    ];

    $pathChecks = ['apache_vhosts_file', 'apache_bin', 'apache_error_log', 'hosts_file', 'mkcert_bin', 'mkcert_dir', 'default_document_root', 'php_ini_file', 'httpd_conf_file'];
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
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight"><i class="fas fa-cogs text-blue-500 mr-2"></i>{{ __('Configurações do Sistema') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Configure os caminhos dos arquivos usados pelo gerenciador de virtual hosts.') }}</p>
    </div>

    <div class="card p-6 max-w-2xl">
        <form action="{{ route('settings.update') }}" method="POST">
            @csrf

            <div class="mb-6 pb-6 border-b border-gray-100 dark:border-gray-700/50">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-400/10 dark:text-blue-400"><i class="fas fa-server text-xs"></i></span>
                    {{ __('Apache') }}
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="label" for="apache_vhosts_file"><i class="fas fa-file-code mr-1.5 text-gray-400"></i>{{ __('Arquivo de Configuração') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="apache_vhosts_file" id="apache_vhosts_file" value="{{ old('apache_vhosts_file', $config['apache_vhosts_file']) }}" class="input flex-1 font-mono text-xs">
                            <span class="shrink-0 text-sm">{{ $exists['apache_vhosts_file'] ?? false ? '✅' : ($exists['apache_vhosts_file'] ?? true ? '—' : '❌') }}</span>
                            <button type="button" onclick="restore('apache_vhosts_file', '{{ $defaults['apache_vhosts_file'] }}')" class="btn-ghost btn-xs p-1.5" title="{{ __('Restaurar padrão') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Ex:') }} C:/Apache24/conf/extra/httpd-vhosts.conf</p>
                    </div>
                    <div>
                        <label class="label" for="apache_bin"><i class="fas fa-cogs mr-1.5 text-gray-400"></i>{{ __('Binário do Apache') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="apache_bin" id="apache_bin" value="{{ old('apache_bin', $config['apache_bin']) }}" class="input flex-1 font-mono text-xs">
                            <span class="shrink-0 text-sm">{{ $exists['apache_bin'] ?? false ? '✅' : ($exists['apache_bin'] ?? true ? '—' : '❌') }}</span>
                            <button type="button" onclick="restore('apache_bin', '{{ $defaults['apache_bin'] }}')" class="btn-ghost btn-xs p-1.5" title="{{ __('Restaurar padrão') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Ex:') }} C:/Apache24/bin/httpd.exe</p>
                    </div>
                    <div>
                        <label class="label" for="apache_service"><i class="fas fa-server mr-1.5 text-gray-400"></i>{{ __('Nome do Serviço') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="apache_service" id="apache_service" value="{{ old('apache_service', $config['apache_service']) }}" class="input flex-1 font-mono text-xs">
                            <span class="shrink-0 text-gray-300 dark:text-gray-600 text-sm">—</span>
                            <button type="button" onclick="restore('apache_service', '{{ $defaults['apache_service'] }}')" class="btn-ghost btn-xs p-1.5" title="{{ __('Restaurar padrão') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Ex:') }} Apache2.4</p>
                    </div>
                    <div>
                        <label class="label" for="apache_ssl_port"><i class="fas fa-lock mr-1.5 text-gray-400"></i>{{ __('Porta SSL') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="number" name="apache_ssl_port" id="apache_ssl_port" value="{{ old('apache_ssl_port', $config['apache_ssl_port']) }}" class="input flex-1 font-mono text-xs">
                            <span class="shrink-0 text-gray-300 dark:text-gray-600 text-sm">—</span>
                            <button type="button" onclick="restore('apache_ssl_port', '{{ $defaults['apache_ssl_port'] }}')" class="btn-ghost btn-xs p-1.5" title="{{ __('Restaurar padrão') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Porta padrão para VirtualHosts SSL (padrão: 443)') }}</p>
                    </div>
                    <div>
                        <label class="label" for="apache_error_log"><i class="fas fa-file-alt mr-1.5 text-gray-400"></i>{{ __('Arquivo de Log de Erros') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="apache_error_log" id="apache_error_log" value="{{ old('apache_error_log', $config['apache_error_log']) }}" class="input flex-1 font-mono text-xs">
                            <span class="shrink-0 text-sm">{{ $exists['apache_error_log'] ?? false ? '✅' : ($exists['apache_error_log'] ?? true ? '—' : '❌') }}</span>
                            <button type="button" onclick="restore('apache_error_log', '{{ $defaults['apache_error_log'] }}')" class="btn-ghost btn-xs p-1.5" title="{{ __('Restaurar padrão') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Usado pelo visualizador de logs.') }} {{ __('Ex:') }} C:/Apache24/logs/error.log</p>
                    </div>
                </div>
            </div>

            <div class="mb-6 pb-6 border-b border-gray-100 dark:border-gray-700/50">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-400/10 dark:text-violet-400"><i class="fas fa-shield-alt text-xs"></i></span>
                    {{ __('SSL / Certificados') }}
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="label" for="mkcert_bin"><i class="fas fa-shield-alt mr-1.5 text-gray-400"></i>{{ __('Binário do mkcert') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="mkcert_bin" id="mkcert_bin" value="{{ old('mkcert_bin', $config['mkcert_bin']) }}" class="input flex-1 font-mono text-xs">
                            <span class="shrink-0 text-sm">{{ $exists['mkcert_bin'] ?? false ? '✅' : ($exists['mkcert_bin'] ?? true ? '—' : '❌') }}</span>
                            <button type="button" onclick="restore('mkcert_bin', '{{ $defaults['mkcert_bin'] }}')" class="btn-ghost btn-xs p-1.5" title="{{ __('Restaurar padrão') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Ex:') }} C:/mkcert/mkcert.exe</p>
                    </div>
                    <div>
                        <label class="label" for="mkcert_dir"><i class="fas fa-folder-open mr-1.5 text-gray-400"></i>{{ __('Diretório de Certificados') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="mkcert_dir" id="mkcert_dir" value="{{ old('mkcert_dir', $config['mkcert_dir']) }}" class="input flex-1 font-mono text-xs">
                            <span class="shrink-0 text-sm">{{ $exists['mkcert_dir'] ?? false ? '✅' : ($exists['mkcert_dir'] ?? true ? '—' : '❌') }}</span>
                            <button type="button" onclick="restore('mkcert_dir', '{{ $defaults['mkcert_dir'] }}')" class="btn-ghost btn-xs p-1.5" title="{{ __('Restaurar padrão') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Ex:') }} C:/mkcert</p>
                    </div>
                </div>
            </div>

            <div class="mb-6 pb-6 border-b border-gray-100 dark:border-gray-700/50">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300"><i class="fas fa-folder text-xs"></i></span>
                    {{ __('Paths') }}
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="label" for="hosts_file"><i class="fas fa-network-wired mr-1.5 text-gray-400"></i>{{ __('Arquivo Hosts') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="hosts_file" id="hosts_file" value="{{ old('hosts_file', $config['hosts_file']) }}" class="input flex-1 font-mono text-xs">
                            <span class="shrink-0 text-sm">{{ $exists['hosts_file'] ?? false ? '✅' : ($exists['hosts_file'] ?? true ? '—' : '❌') }}</span>
                            <button type="button" onclick="restore('hosts_file', '{{ $defaults['hosts_file'] }}')" class="btn-ghost btn-xs p-1.5" title="{{ __('Restaurar padrão') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Ex:') }} C:/Windows/System32/drivers/etc/hosts</p>
                    </div>
                    <div>
                        <label class="label" for="default_document_root"><i class="fas fa-folder mr-1.5 text-gray-400"></i>{{ __('Diretório Raiz Padrão') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="default_document_root" id="default_document_root" value="{{ old('default_document_root', $config['default_document_root']) }}" class="input flex-1 font-mono text-xs">
                            <span class="shrink-0 text-sm">{{ $exists['default_document_root'] ?? false ? '✅' : ($exists['default_document_root'] ?? true ? '—' : '❌') }}</span>
                            <button type="button" onclick="restore('default_document_root', '{{ $defaults['default_document_root'] }}')" class="btn-ghost btn-xs p-1.5" title="{{ __('Restaurar padrão') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Usado como valor padrão ao criar novo host.') }} {{ __('Ex:') }} D:/www/</p>
                    </div>
                    <div>
                        <label class="label" for="php_ini_file"><i class="fab fa-php mr-1.5 text-gray-400"></i>{{ __('php.ini') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="php_ini_file" id="php_ini_file" value="{{ old('php_ini_file', $config['php_ini_file']) }}" class="input flex-1 font-mono text-xs">
                            <span class="shrink-0 text-sm">{{ $exists['php_ini_file'] ?? false ? '✅' : ($exists['php_ini_file'] ?? true ? '—' : '❌') }}</span>
                            <button type="button" onclick="restore('php_ini_file', '{{ $defaults['php_ini_file'] }}')" class="btn-ghost btn-xs p-1.5" title="{{ __('Restaurar padrão') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Arquivo de configuração do PHP.') }} {{ __('Ex:') }} C:/php/php.ini</p>
                    </div>
                    <div>
                        <label class="label" for="httpd_conf_file"><i class="fas fa-server mr-1.5 text-gray-400"></i>{{ __('httpd.conf') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="httpd_conf_file" id="httpd_conf_file" value="{{ old('httpd_conf_file', $config['httpd_conf_file']) }}" class="input flex-1 font-mono text-xs">
                            <span class="shrink-0 text-sm">{{ $exists['httpd_conf_file'] ?? false ? '✅' : ($exists['httpd_conf_file'] ?? true ? '—' : '❌') }}</span>
                            <button type="button" onclick="restore('httpd_conf_file', '{{ $defaults['httpd_conf_file'] }}')" class="btn-ghost btn-xs p-1.5" title="{{ __('Restaurar padrão') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Arquivo de configuração principal do Apache.') }} {{ __('Ex:') }} C:/Apache24/conf/httpd.conf</p>
                    </div>
                </div>
            </div>

            <div class="mb-6 pb-6 border-b border-gray-100 dark:border-gray-700/50">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-400/10 dark:text-sky-400"><i class="fas fa-link text-xs"></i></span>
                    {{ __('Integrações') }}
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="label" for="phpmyadmin_url"><i class="fas fa-database mr-1.5 text-gray-400"></i>{{ __('URL do phpMyAdmin') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="phpmyadmin_url" id="phpmyadmin_url" value="{{ old('phpmyadmin_url', $config['phpmyadmin_url']) }}" class="input flex-1 font-mono text-xs" placeholder="http://localhost/phpmyadmin">
                            <span class="shrink-0 text-gray-300 dark:text-gray-600 text-sm">—</span>
                            <button type="button" onclick="restore('phpmyadmin_url', '')" class="btn-ghost btn-xs p-1.5" title="{{ __('Limpar') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Usado para atalho rápido no dashboard.') }}</p>
                    </div>
                    <div>
                        <label class="label" for="phpmyadmin_user"><i class="fas fa-user mr-1.5 text-gray-400"></i>{{ __('Usuário phpMyAdmin') }}</label>
                        <input type="text" name="phpmyadmin_user" id="phpmyadmin_user" value="{{ old('phpmyadmin_user', $config['phpmyadmin_user'] ?? 'root') }}" class="input flex-1 font-mono text-xs" placeholder="root">
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Usado para auto-login no phpMyAdmin.') }}</p>
                    </div>
                    <div>
                        <label class="label" for="phpmyadmin_password"><i class="fas fa-lock mr-1.5 text-gray-400"></i>{{ __('Senha phpMyAdmin') }}</label>
                        <input type="password" name="phpmyadmin_password" id="phpmyadmin_password" value="{{ old('phpmyadmin_password', $config['phpmyadmin_password'] ?? '') }}" class="input flex-1 font-mono text-xs" placeholder="{{ __('Deixe em branco se não houver senha') }}">
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Usado para auto-login no phpMyAdmin.') }}</p>
                    </div>
                    <div>
                        <label class="label" for="vscode_executable"><i class="fas fa-code mr-1.5 text-gray-400"></i>{{ __('Comando VS Code') }}</label>
                        <div class="flex gap-2 items-center">
                            <input type="text" name="vscode_executable" id="vscode_executable" value="{{ old('vscode_executable', $config['vscode_executable']) }}" class="input flex-1 font-mono text-xs">
                            <span class="shrink-0 text-gray-300 dark:text-gray-600 text-sm">—</span>
                            <button type="button" onclick="restore('vscode_executable', 'code')" class="btn-ghost btn-xs p-1.5" title="{{ __('Restaurar padrão') }}">↩</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">{{ __('Comando para abrir o VS Code.') }} {{ __('Padrão:') }} <code>code</code></p>
                    </div>
                </div>
            </div>

            <div class="mb-6 p-4 rounded-xl bg-blue-50/80 border border-blue-200/60 dark:bg-blue-950/20 dark:border-blue-800/30">
                <p class="text-xs text-blue-700 dark:text-blue-300"><span class="font-semibold">{{ __('💡 Certificados SSL:') }}</span> {{ __('Se o navegador não confiar nos certificados, execute no PowerShell como Administrador:') }}</p>
                <code class="block mt-2 text-xs bg-blue-100/80 dark:bg-blue-900/30 px-3 py-2 rounded-lg text-blue-700 dark:text-blue-300 font-mono">$env:CAROOT="D:\www\localserver\storage\app\mkcert"; mkcert -install</code>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> {{ __('Salvar Configurações') }}
                </button>
                <a href="{{ route('virtual-hosts.index') }}" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('Voltar') }}
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
