<?php

namespace App\Services;

use App\Models\VirtualHost;
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
        $this->apache->writeConfig($allVhosts);

        $test = $this->apache->testConfig();

        if (!$test['success']) {
            $errOutput = $test['output'];
            $isSslError = str_contains($errOutput, 'AH00141') || str_contains($errOutput, 'random number generator');

            if (!$isSslError) {
                throw new RuntimeException('Erro na configuração do Apache: ' . $errOutput);
            }
        }

        $restart = $this->apache->restart();
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
