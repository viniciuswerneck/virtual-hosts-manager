<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Throwable;

class ApacheConfigService
{
    private string $configPath;

    private string $apacheBin;

    private string $apacheService;

    private array $directiveMeta = [];

    public function __construct()
    {
        $this->configPath = config('virtualhosts.httpd_conf_file');
        $this->apacheBin = config('virtualhosts.apache_bin', 'C:/Apache24/bin/httpd.exe');
        $this->apacheService = config('virtualhosts.apache_service', 'Apache2.4');

        if (! $this->configPath || ! file_exists($this->configPath)) {
            $derived = preg_replace('#bin/httpd\.exe$#i', 'conf/httpd.conf', $this->apacheBin);
            $this->configPath = $derived !== $this->apacheBin && file_exists($derived)
                ? str_replace('\\', '/', $derived)
                : 'C:/Apache24/conf/httpd.conf';
        }

        $this->directiveMeta = $this->buildDirectiveMeta();
    }

    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    public function fileExists(): bool
    {
        return File::exists($this->configPath);
    }

    public function getDirectives(): array
    {
        if (! $this->fileExists()) {
            return [];
        }

        $lines = $this->readLines();
        $inSection = 0;
        $result = [];

        foreach ($this->directiveMeta as $key => $meta) {
            $result[$key] = array_merge($meta, [
                'value' => $meta['default'],
                'found' => false,
                'line' => null,
            ]);
        }

        foreach ($lines as $i => $line) {
            $trimmed = trim($line);

            if (preg_match('/^<(\w+)/i', $trimmed)) {
                $inSection++;

                continue;
            }

            if (preg_match('/^<\/(\w+)/i', $trimmed)) {
                $inSection = max(0, $inSection - 1);

                continue;
            }

            if ($inSection > 0) {
                continue;
            }

            foreach ($this->directiveMeta as $key => $meta) {
                if ($result[$key]['found']) {
                    continue;
                }

                if (preg_match('/^\s*#?\s*'.preg_quote($key, '/').'\s+/i', $line)) {
                    $val = trim(substr($line, strlen($key)));
                    $val = trim($val, '"\' ');
                    $result[$key]['value'] = $val;
                    $result[$key]['found'] = true;
                    $result[$key]['line'] = $i;
                }
            }
        }

        return $result;
    }

    public function updateDirectives(array $data): void
    {
        $lines = $this->readLines();

        foreach ($data as $key => $item) {
            if (! isset($this->directiveMeta[$key])) {
                continue;
            }

            $value = $item['value'] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $this->setTopDirective($lines, $key, $value);
        }

        $this->writeLines($lines);
    }

    public function getModules(): array
    {
        if (! $this->fileExists()) {
            return [];
        }

        $lines = $this->readLines();
        $modules = [];

        foreach ($lines as $i => $line) {
            if (preg_match('/^\s*#?\s*LoadModule\s+(\S+)\s+(\S+)/i', $line, $m)) {
                $moduleName = $m[1];
                $modulePath = $m[2];
                $modules[$moduleName] = [
                    'name' => $moduleName,
                    'path' => $modulePath,
                    'enabled' => ! preg_match('/^\s*#/', $line),
                    'line' => $i,
                ];
            }
        }

        ksort($modules);

        return $modules;
    }

    public function toggleModule(string $moduleName, bool $enable): void
    {
        $lines = $this->readLines();

        foreach ($lines as $i => $line) {
            if (preg_match('/^\s*#?\s*LoadModule\s+'.preg_quote($moduleName, '/').'\s+/i', $line)) {
                if ($enable) {
                    $lines[$i] = preg_replace('/^\s*#\s*/', '', $line, 1);
                } elseif (! preg_match('/^\s*#/', $line)) {
                    $lines[$i] = '# '.$line;
                }
                $this->writeLines($lines);

                return;
            }
        }

        throw new \RuntimeException("Módulo '{$moduleName}' não encontrado no httpd.conf.");
    }

    public function testConfig(): array
    {
        $cmd = sprintf('"%s" -t 2>&1', $this->apacheBin);
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);
        $text = implode("\n", $output);

        return [
            'success' => $exitCode === 0,
            'output' => $text,
        ];
    }

    public function restart(): array
    {
        $apache = app(ApacheService::class);

        return $apache->restart();
    }

    private function readLines(): array
    {
        $content = File::get($this->configPath);

        return explode("\n", str_replace(["\r\n", "\r"], "\n", $content));
    }

    private function writeLines(array $lines): void
    {
        try {
            File::put($this->configPath, implode("\n", $lines));
        } catch (Throwable $e) {
            throw new \RuntimeException(
                "Permissão negada ao escrever em {$this->configPath}."
            );
        }
    }

    private function setTopDirective(array &$lines, string $key, string $value): void
    {
        $inSection = 0;

        foreach ($lines as $i => $line) {
            $trimmed = trim($line);

            if (preg_match('/^<(\w+)/i', $trimmed)) {
                $inSection++;

                continue;
            }

            if (preg_match('/^<\/(\w+)/i', $trimmed)) {
                $inSection = max(0, $inSection - 1);

                continue;
            }

            if ($inSection > 0) {
                continue;
            }

            if (preg_match('/^\s*#?\s*'.preg_quote($key, '/').'\s+/i', $line)) {
                $needsQuote = str_contains($value, ' ') || str_contains($value, '/');
                $q = $needsQuote ? '"' : '';
                $prefix = preg_match('/^\s*#/', $line) ? '# ' : '';
                $lines[$i] = $prefix.$key.' '.$q.$value.$q;

                return;
            }
        }

        $needsQuote = str_contains($value, ' ') || str_contains($value, '/');
        $q = $needsQuote ? '"' : '';
        $lines[] = $key.' '.$q.$value.$q;
    }

    private function buildDirectiveMeta(): array
    {
        return [
            'ServerName' => [
                'section' => 'Servidor',
                'label' => 'Nome do Servidor',
                'type' => 'string',
                'default' => 'localhost',
                'description' => 'Nome do servidor usado pelo Apache.',
            ],
            'ServerAdmin' => [
                'section' => 'Servidor',
                'label' => 'Email do Admin',
                'type' => 'string',
                'default' => 'admin@localhost.com',
                'description' => 'Email exibido em mensagens de erro.',
            ],
            'ServerRoot' => [
                'section' => 'Servidor',
                'label' => 'Diretório Raiz',
                'type' => 'string',
                'default' => 'C:/Apache24',
                'description' => 'Diretório raiz da instalação do Apache.',
            ],
            'DocumentRoot' => [
                'section' => 'Servidor',
                'label' => 'Document Root',
                'type' => 'string',
                'default' => 'C:/Apache24/htdocs',
                'description' => 'Diretório raiz padrão para servir arquivos.',
            ],
            'Listen' => [
                'section' => 'Conexões',
                'label' => 'Porta de Escuta',
                'type' => 'string',
                'default' => '80',
                'description' => 'Porta na qual o Apache escuta conexões.',
            ],
            'Timeout' => [
                'section' => 'Conexões',
                'label' => 'Timeout',
                'type' => 'integer',
                'default' => '60',
                'description' => 'Tempo em segundos para timeout de conexão.',
            ],
            'KeepAlive' => [
                'section' => 'Conexões',
                'label' => 'Keep-Alive',
                'type' => 'select',
                'options' => ['On', 'Off'],
                'default' => 'On',
                'description' => 'Permite conexões persistentes.',
            ],
            'MaxKeepAliveRequests' => [
                'section' => 'Conexões',
                'label' => 'Máx. Requisições Keep-Alive',
                'type' => 'integer',
                'default' => '100',
                'description' => 'Requisições máximas por conexão keep-alive.',
            ],
            'KeepAliveTimeout' => [
                'section' => 'Conexões',
                'label' => 'Timeout Keep-Alive',
                'type' => 'integer',
                'default' => '5',
                'description' => 'Segundos entre requisições keep-alive.',
            ],
            'ErrorLog' => [
                'section' => 'Logs',
                'label' => 'Arquivo de Log de Erros',
                'type' => 'string',
                'default' => 'logs/error.log',
                'description' => 'Caminho do arquivo de log de erros.',
            ],
            'LogLevel' => [
                'section' => 'Logs',
                'label' => 'Nível de Log',
                'type' => 'select',
                'options' => ['emerg', 'alert', 'crit', 'error', 'warn', 'notice', 'info', 'debug'],
                'default' => 'warn',
                'description' => 'Severidade mínima para registrar no log.',
            ],
            'HostnameLookups' => [
                'section' => 'Logs',
                'label' => 'Resolução de Hostname',
                'type' => 'select',
                'options' => ['On', 'Off', 'Double'],
                'default' => 'Off',
                'description' => 'Habilita resolução DNS nos logs.',
            ],
        ];
    }
}
