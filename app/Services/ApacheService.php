<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

class ApacheService
{
    private string $vhostsFile;
    private string $apacheBin;
    private string $apacheService;
    private int $sslPort;

    public function __construct()
    {
        $this->vhostsFile = config('virtualhosts.apache_vhosts_file');
        $this->apacheBin = config('virtualhosts.apache_bin');
        $this->apacheService = config('virtualhosts.apache_service', 'Apache2.4');
        $this->sslPort = (int) config('virtualhosts.apache_ssl_port', 443);
    }

    public function getVhostsFile(): string
    {
        return $this->vhostsFile;
    }

    public function parseExisting(): array
    {
        if (!File::exists($this->vhostsFile)) {
            return [];
        }

        $content = File::get($this->vhostsFile);
        $vhosts = [];
        $pattern = '/<VirtualHost \*:(\d+)>\s+ServerName\s+(\S+)\s+DocumentRoot\s+"([^"]+)"/i';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = $match[2];
                if (!isset($vhosts[$name])) {
                    $vhosts[$name] = [
                        'server_name' => $name,
                        'document_root' => $match[3],
                        'port' => (int) $match[1],
                        'ssl_enabled' => false,
                    ];
                }
                if ($match[1] === '443') {
                    $vhosts[$name]['ssl_enabled'] = true;
                }
            }
        }

        return array_values($vhosts);
    }

    public function writeConfig(array $vhosts): void
    {
        $lines = [];
        $lines[] = '# =========================================================';
        $lines[] = '# Virtual Hosts Configuration';
        $lines[] = '# =========================================================';
        $lines[] = '# Gerenciado pelo Laravel Hosts Manager';
        $lines[] = '# =========================================================';
        $lines[] = '';

        foreach ($vhosts as $vhost) {
            $name = $vhost['server_name'];
            $root = $vhost['document_root'];
            $port = $vhost['port'] ?? 80;

            $lines[] = '# -----------------------------';
            $lines[] = "# {$name}";
            $lines[] = '# -----------------------------';
            $lines[] = "<VirtualHost *:{$port}>";
            $lines[] = "    ServerName {$name}";
            $lines[] = "    DocumentRoot \"{$root}\"";
            $lines[] = '';
            $lines[] = "    <Directory \"{$root}\">";
            $lines[] = '        AllowOverride All';
            $lines[] = '        Require all granted';
            $lines[] = '    </Directory>';
            $lines[] = '</VirtualHost>';
            $lines[] = '';

            if ($vhost['ssl_enabled']) {
                $lines[] = "<VirtualHost *:{$this->sslPort}>";
                $lines[] = "    ServerName {$name}";
                $lines[] = "    DocumentRoot \"{$root}\"";
                $lines[] = '';
                $lines[] = "    <Directory \"{$root}\">";
                $lines[] = '        Options Indexes FollowSymLinks';
                $lines[] = '        AllowOverride All';
                $lines[] = '        Require all granted';
                $lines[] = '    </Directory>';
                $lines[] = '';
                $lines[] = '    SSLEngine on';
                $certDir = rtrim(config('virtualhosts.mkcert_dir'), '/');
                $safeName = str_replace(['/', '\\', '..'], '', $name);
                $lines[] = "    SSLCertificateFile \"{$certDir}/{$safeName}.pem\"";
                $lines[] = "    SSLCertificateKeyFile \"{$certDir}/{$safeName}-key.pem\"";
                $lines[] = '</VirtualHost>';
                $lines[] = '';
            }
        }

        try {
            File::put($this->vhostsFile, implode("\n", $lines));
        } catch (Throwable $e) {
            throw new \RuntimeException(
                "Permissão negada ao escrever no Apache config ({$this->vhostsFile}).\n" .
                "Execute o fix-permissions.bat como Administrador para liberar as permissões."
            );
        }
    }

    public function restart(): array
    {
        $output = $this->safeRun([$this->apacheBin, '-k', 'restart'], 5);
        $httpdOutput = $output !== null ? $output : '';

        if ($this->waitForRunning(3)) {
            return ['success' => true, 'output' => $httpdOutput];
        }

        $this->safeRun(['taskkill', '/F', '/IM', 'httpd.exe'], 3);
        $this->waitForNotRunning(2);

        $startOutput = $this->safeRun([$this->apacheBin], 5);

        if ($this->waitForRunning(3)) {
            return ['success' => true, 'output' => 'Apache reiniciado manualmente.'];
        }

        $this->safeRun(['net', 'stop', $this->apacheService], 5);
        $this->waitForNotRunning(2);

        $this->safeRun(['net', 'start', $this->apacheService], 5);

        if ($this->waitForRunning(5)) {
            return ['success' => true, 'output' => "Apache reiniciado via servico {$this->apacheService}."];
        }

        $errorMsg = $startOutput ?: 'Falha ao iniciar Apache (sem resposta do binário)';
        return ['success' => false, 'output' => $errorMsg];
    }

    private function safeRun(array $command, int $timeout): ?string
    {
        try {
            $process = new Process($command);
            $process->setTimeout($timeout);
            $process->run();
            return $process->getOutput() ?: $process->getErrorOutput();
        } catch (\Throwable) {
            return null;
        }
    }

    private function waitForRunning(int $timeoutSeconds): bool
    {
        $deadline = microtime(true) + $timeoutSeconds;
        while (microtime(true) < $deadline) {
            if ($this->isRunning()) {
                return true;
            }
            usleep(200000);
        }
        return $this->isRunning();
    }

    private function waitForNotRunning(int $timeoutSeconds): bool
    {
        $deadline = microtime(true) + $timeoutSeconds;
        while (microtime(true) < $deadline) {
            if (!$this->isRunning()) {
                return true;
            }
            usleep(200000);
        }
        return !$this->isRunning();
    }

    public function isRunning(): bool
    {
        return count($this->tasklist()) >= 2;
    }

    private function tasklist(): array
    {
        try {
            $process = new Process(['tasklist', '/NH', '/FI', 'IMAGENAME eq httpd.exe']);
            $process->setTimeout(3);
            $process->run();
            return $process->isSuccessful() ? array_filter(explode("\n", $process->getOutput())) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function testConfig(): array
    {
        $attempts = 2;
        $delay = 500000;

        for ($i = 0; $i < $attempts; $i++) {
            $process = new Process([$this->apacheBin, '-t']);
            $process->setTimeout(5);
            $process->run();

            if ($process->isSuccessful()) {
                return [
                    'success' => true,
                    'output' => $process->getOutput() ?: $process->getErrorOutput(),
                ];
            }

            if ($i < $attempts - 1) {
                usleep($delay);
            }
        }

        return [
            'success' => false,
            'output' => $process->getOutput() ?: $process->getErrorOutput(),
        ];
    }
}
