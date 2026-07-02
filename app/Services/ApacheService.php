<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
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

        $lines[] = '# -----------------------------';
        $lines[] = '# Catch-all padrao (dominios nao configurados / desativados)';
        $lines[] = '# -----------------------------';
        $lines[] = '<VirtualHost *:80>';
        $lines[] = '    ServerName _default_';
        $lines[] = '    DocumentRoot "D:/www/_default/public"';
        $lines[] = '';
        $lines[] = '    <Directory "D:/www/_default/public">';
        $lines[] = '        AllowOverride All';
        $lines[] = '        Require all granted';
        $lines[] = '    </Directory>';
        $lines[] = '</VirtualHost>';
        $lines[] = '';

        $certDir = rtrim(config('virtualhosts.mkcert_dir'), '/');
        $defaultCert = "{$certDir}/localhost.pem";
        $defaultKey = "{$certDir}/localhost-key.pem";
        if (File::exists($defaultCert) && File::exists($defaultKey)) {
            $lines[] = '<VirtualHost *:443>';
            $lines[] = '    ServerName _default_';
            $lines[] = '    DocumentRoot "D:/www/_default/public"';
            $lines[] = '';
            $lines[] = '    <Directory "D:/www/_default/public">';
            $lines[] = '        AllowOverride All';
            $lines[] = '        Require all granted';
            $lines[] = '    </Directory>';
            $lines[] = '    SSLEngine on';
            $lines[] = "    SSLCertificateFile \"{$defaultCert}\"";
            $lines[] = "    SSLCertificateKeyFile \"{$defaultKey}\"";
            $lines[] = '</VirtualHost>';
            $lines[] = '';
        }

        $lines[] = '# =========================================================';
        $lines[] = '# Virtual Hosts dos projetos';
        $lines[] = '# =========================================================';
        $lines[] = '';

        foreach ($vhosts as $vhost) {
            if (isset($vhost['active']) && !$vhost['active']) {
                continue;
            }

            $name = $vhost['server_name'];
            $root = $vhost['document_root'];
            $port = $vhost['port'] ?? 80;
            $phpVersion = $vhost['php_version'] ?? null;

            $lines[] = '# -----------------------------';
            $lines[] = "# {$name}";
            $lines[] = '# -----------------------------';
            $lines[] = "<VirtualHost *:{$port}>";
            $lines[] = "    ServerName {$name}";
            $lines[] = "    DocumentRoot \"{$root}\"";
            $lines[] = '';

            if ($phpVersion) {
                $phpVersionClean = str_replace('.', '', $phpVersion);
                $lines[] = "    <FilesMatch \.php$>";
                $lines[] = "        SetHandler \"fcgid://php-cgi{$phpVersionClean}/php-cgi.exe\"";
                $lines[] = '    </FilesMatch>';
                $lines[] = '';
            }

            $lines[] = "    <Directory \"{$root}\">";
            $lines[] = '        AllowOverride All';
            $lines[] = '        Require all granted';
            $lines[] = '    </Directory>';
            $lines[] = '</VirtualHost>';
            $lines[] = '';

            if ($vhost['ssl_enabled']) {
                $certDir = rtrim(config('virtualhosts.mkcert_dir'), '/');
                $safeName = str_replace(['/', '\\', '..'], '', $name);
                $certFile = "{$certDir}/{$safeName}.pem";
                $keyFile = "{$certDir}/{$safeName}-key.pem";

                if (!File::exists($certFile) || !File::exists($keyFile)) {
                    continue;
                }

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
                $lines[] = "    SSLCertificateFile \"{$certFile}\"";
                $lines[] = "    SSLCertificateKeyFile \"{$keyFile}\"";
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
        set_time_limit(60);

        $isRunning = $this->isRunning();

        // 1. Se está rodando, tenta httpd -k restart (funciona sem admin)
        if ($isRunning) {
            exec(sprintf('"%s" -k restart 2>&1', $this->apacheBin), $httpdOutputArr, $httpdExit);
            $httpdOutput = $httpdOutputArr ? implode("\n", $httpdOutputArr) : null;

            if ($httpdExit === 0 && $this->waitForRunning(10)) {
                return ['success' => true, 'output' => $httpdOutput ?: 'Apache reiniciado via httpd -k restart.'];
            }

            // Se httpd -k restart falhou, tenta via serviço
            $stopOutput = $this->safeRunShell("net stop {$this->apacheService}", 15);
            if ($stopOutput !== null && !$this->isAccessDenied($stopOutput)) {
                $this->waitForNotRunning(5);
                $startOutput = $this->safeRunShell("net start {$this->apacheService}", 15);
                if ($startOutput && !$this->isAccessDenied($startOutput)) {
                    if ($this->waitForRunning(10)) {
                        return ['success' => true, 'output' => "Apache reiniciado via serviço {$this->apacheService}."];
                    }
                }
            }

            // Tenta PowerShell elevado
            $result = $this->restartViaElevatedPowerShell();
            if ($result !== null) {
                return $result;
            }

            // Último recurso: taskkill + iniciar direto
            $this->safeRunShell("taskkill /F /IM httpd.exe", 5);
            $this->waitForNotRunning(5);
            $this->runApacheBackground();
            if ($this->waitForRunning(10)) {
                return ['success' => true, 'output' => 'Apache reiniciado via taskkill + httpd.'];
            }

            return [
                'success' => false,
                'output' => 'Falha ao reiniciar Apache. Execute como Administrador: net stop ' . $this->apacheService . ' && net start ' . $this->apacheService,
            ];
        }

        // 2. Apache não está rodando — tenta iniciar
        $startOutput = $this->safeRunShell("net start {$this->apacheService}", 15);
        if ($startOutput && !$this->isAccessDenied($startOutput)) {
            if ($this->waitForRunning(10)) {
                return ['success' => true, 'output' => "Apache iniciado via serviço {$this->apacheService}."];
            }
        }

        // Tenta via PowerShell elevado
        $result = $this->restartViaElevatedPowerShell();
        if ($result !== null) {
            return $result;
        }

        // Tenta iniciar binário direto
        $this->runApacheBackground();
        if ($this->waitForRunning(10)) {
            return ['success' => true, 'output' => 'Apache iniciado via httpd direto.'];
        }

        return [
            'success' => false,
            'output' => 'Falha ao iniciar Apache. Execute como Administrador no PowerShell: net start ' . $this->apacheService,
        ];
    }

    private function isAccessDenied(string $output): bool
    {
        return str_contains($output, 'Acesso negado') || str_contains($output, 'Access denied');
    }

    private function restartViaElevatedPowerShell(): ?array
    {
        $script = "Stop-Service -Name '{$this->apacheService}'; Start-Service -Name '{$this->apacheService}'";
        $encoded = base64_encode(mb_convert_encoding($script, 'UTF-16LE'));

        $psArgs = "-NoProfile -ExecutionPolicy Bypass -EncodedCommand {$encoded}";
        $cmd = "powershell -NoProfile -ExecutionPolicy Bypass -Command \"Start-Process powershell -ArgumentList '{$psArgs}' -Verb RunAs -Wait\" 2>&1";

        try {
            set_time_limit(40);
            exec($cmd, $output, $exitCode);
            $outputStr = $output ? implode("\n", $output) : null;

            if ($this->waitForRunning(10)) {
                return ['success' => true, 'output' => 'Apache reiniciado via PowerShell elevado.'];
            }

            if ($outputStr && $this->isAccessDenied($outputStr)) {
                return null;
            }
        } catch (\Throwable) {
        }

        return null;
    }

    private function safeRunShell(string $command, int $timeout): ?string
    {
        try {
            set_time_limit($timeout + 10);
            exec($command . ' 2>&1', $output, $exitCode);
            return $output ? implode("\n", $output) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function runApacheBackground(): ?string
    {
        try {
            $shell = new \COM("WScript.Shell");
            $shell->Run($this->apacheBin, 0, false);
            return null;
        } catch (\Throwable) {
            try {
                $cmd = sprintf('start "" /B "%s" 2>&1', $this->apacheBin);
                exec($cmd, $output, $exitCode);
                return $output ? implode("\n", $output) : null;
            } catch (\Throwable) {
                return null;
            }
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
        $lines = $this->tasklist();
        foreach ($lines as $line) {
            if (stripos($line, 'httpd.exe') !== false) {
                return true;
            }
        }
        return false;
    }

    private function tasklist(): array
    {
        @exec('tasklist /NH /FI "IMAGENAME eq httpd.exe" 2>&1', $output, $exitCode);
        return $exitCode === 0 ? array_filter($output) : [];
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
}
