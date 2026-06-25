<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Throwable;

class ApacheService
{
    private string $vhostsFile;
    private string $apacheBin;

    public function __construct()
    {
        $this->vhostsFile = 'C:/Apache24/conf/extra/httpd-vhosts.conf';
        $this->apacheBin = 'C:/Apache24/bin/httpd.exe';
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
                $lines[] = "    SSLCertificateFile \"C:/mkcert/{$name}.pem\"";
                $lines[] = "    SSLCertificateKeyFile \"C:/mkcert/{$name}-key.pem\"";
                $lines[] = '</VirtualHost>';
                $lines[] = '';
            }
        }

        try {
            File::put($this->vhostsFile, implode("\n", $lines));
        } catch (Throwable $e) {
            throw new \RuntimeException(
                "Permissão negada ao escrever no Apache config ({$this->vhostsFile}).\n" .
                "Execute o fix-permissions.bat como Administrador."
            );
        }
    }

    public function restart(): array
    {
        $output = [];
        $returnVar = 0;

        exec("\"{$this->apacheBin}\" -k restart 2>&1", $output, $returnVar);
        sleep(1);

        if ($returnVar === 0 && $this->isRunning()) {
            return ['success' => true, 'output' => implode("\n", $output)];
        }

        $taskOut = $this->tasklist();
        if (count($taskOut) >= 2) {
            exec("taskkill /F /IM httpd.exe 2>&1", $killOut, $killVar);
            sleep(1);
        }

        exec("\"{$this->apacheBin}\" 2>&1", $startOut, $startVar);
        sleep(2);

        if ($this->isRunning()) {
            return ['success' => true, 'output' => 'Apache reiniciado manualmente.'];
        }

        $errorMsg = !empty($startOut) ? implode("\n", $startOut) : 'Falha ao iniciar Apache (sem resposta do binário)';
        return ['success' => false, 'output' => $errorMsg];
    }

    public function isRunning(): bool
    {
        return count($this->tasklist()) >= 2;
    }

    private function tasklist(): array
    {
        exec('tasklist /NH /FI "IMAGENAME eq httpd.exe" 2>&1', $out, $code);
        return $code === 0 ? $out : [];
    }

    public function testConfig(): array
    {
        $output = [];
        $returnVar = 0;
        exec("\"{$this->apacheBin}\" -t 2>&1", $output, $returnVar);
        return [
            'success' => $returnVar === 0,
            'output' => implode("\n", $output),
        ];
    }
}
