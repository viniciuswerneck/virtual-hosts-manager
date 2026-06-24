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

    public function __construct()
    {
        $this->vhostsFile = config('virtualhosts.apache_vhosts_file');
        $this->apacheBin = config('virtualhosts.apache_bin');
        $this->apacheService = config('virtualhosts.apache_service', 'Apache2.4');
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
                $lines[] = "<VirtualHost *:443>";
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
                $lines[] = "    SSLCertificateFile \"{$certDir}/{$name}.pem\"";
                $lines[] = "    SSLCertificateKeyFile \"{$certDir}/{$name}-key.pem\"";
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
        $process = new Process([$this->apacheBin, '-k', 'restart']);
        $process->run();
        sleep(1);

        if ($process->isSuccessful() && $this->isRunning()) {
            $output = $process->getOutput() ?: $process->getErrorOutput();
            return ['success' => true, 'output' => $output];
        }

        if ($this->isRunning()) {
            $kill = new Process(['taskkill', '/F', '/IM', 'httpd.exe']);
            $kill->run();
            sleep(1);
        }

        $start = new Process([$this->apacheBin]);
        $start->run();
        sleep(2);

        if ($this->isRunning()) {
            return ['success' => true, 'output' => 'Apache reiniciado manualmente.'];
        }

        $netStop = new Process(['net', 'stop', $this->apacheService]);
        $netStop->run();
        sleep(1);

        $netStart = new Process(['net', 'start', $this->apacheService]);
        $netStart->run();
        sleep(2);

        if ($this->isRunning()) {
            return ['success' => true, 'output' => "Apache reiniciado via servico {$this->apacheService}."];
        }

        $errorMsg = $start->getOutput() ?: $start->getErrorOutput() ?: 'Falha ao iniciar Apache (sem resposta do binário)';
        return ['success' => false, 'output' => $errorMsg];
    }

    public function isRunning(): bool
    {
        return count($this->tasklist()) >= 2;
    }

    private function tasklist(): array
    {
        $process = new Process(['tasklist', '/NH', '/FI', 'IMAGENAME eq httpd.exe']);
        $process->run();
        return $process->isSuccessful() ? array_filter(explode("\n", $process->getOutput())) : [];
    }

    public function testConfig(): array
    {
        $attempts = 3;
        $delay = 1000000;

        for ($i = 0; $i < $attempts; $i++) {
            $process = new Process([$this->apacheBin, '-t']);
            $process->setTimeout(10);
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
