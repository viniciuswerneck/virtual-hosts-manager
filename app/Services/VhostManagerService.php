<?php

namespace App\Services;

use App\Models\VirtualHost;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use RuntimeException;

class VhostManagerService
{
    public function __construct(
        private ApacheService $apache,
        private HostsFileService $hosts,
        private MkcertService $mkcert,
    ) {}

    public function applyApacheConfig(): array
    {
        $allVhosts = VirtualHost::all()->toArray();
        $vhostsFile = $this->apache->getVhostsFile();

        $this->apache->writeConfig($allVhosts);

        try {
            $test = $this->apache->testConfig();
        } catch (\Throwable) {
            $service = config('virtualhosts.apache_service');
            return [
                'type' => 'warning',
                'message' => "Configuração do Apache aplicada, mas o teste de sintaxe excedeu o tempo limite. O Apache pode precisar ser reiniciado manualmente: net stop {$service} && net start {$service}",
            ];
        }

        if (!$test['success']) {
            $errOutput = $test['output'];
            $isSslError = str_contains($errOutput, 'AH00141') || str_contains($errOutput, 'random number generator');

            if (!$isSslError) {
                return [
                    'type' => 'warning',
                    'message' => 'A configuração foi salva, mas o Apache reportou um erro de sintaxe: ' . $errOutput,
                ];
            }
        }

        Cache::forget('apache_running');

        try {
            $restart = $this->apache->restart();
        } catch (\Throwable) {
            $service = config('virtualhosts.apache_service');
            return [
                'type' => 'warning',
                'message' => "Configuração aplicada, mas o Apache não pôde ser reiniciado (tempo excedido). Execute manualmente como Administrador: net stop {$service} && net start {$service}",
            ];
        }

        if ($restart['success']) {
            return ['type' => 'success', 'message' => 'Apache reiniciado automaticamente.'];
        }

        $msg = $restart['output'];
        $service = config('virtualhosts.apache_service');

        if (str_contains($msg, 'Acesso negado') || str_contains($msg, 'Access denied')) {
            return [
                'type' => 'warning',
                'message' => "Apache precisa ser reiniciado manualmente como Administrador. No PowerShell Admin: net stop {$service} && net start {$service}",
            ];
        }

        if (str_contains($msg, 'AH00141') || str_contains($msg, 'random number generator')) {
            return [
                'type' => 'warning',
                'message' => "Apache com erro de SSL (AH00141). O Apache foi configurado, mas precisa ser reiniciado manualmente como Administrador: net stop {$service} && net start {$service}. Se o erro persistir, comente 'LoadModule ssl_module' no httpd.conf se nao precisar de SSL.",
            ];
        }

        return ['type' => 'warning', 'message' => 'Aviso: ' . $msg];
    }

    public function syncFromApache(): int
    {
        $apacheVhosts = $this->apache->parseExisting();
        $count = 0;

        foreach ($apacheVhosts as $v) {
            VirtualHost::firstOrCreate(
                ['server_name' => $v['server_name']],
                [
                    'document_root' => $v['document_root'],
                    'ssl_enabled' => $v['ssl_enabled'],
                    'port' => $v['port'],
                ]
            );
            $count++;
        }

        return $count;
    }

    public function regenerateCert(VirtualHost $virtualHost): array
    {
        $name = $virtualHost->server_name;

        $this->mkcert->delete($name);
        return $this->mkcert->generate($name);
    }
}
