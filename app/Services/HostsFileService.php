<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Throwable;

class HostsFileService
{
    private string $hostsFile;

    public function __construct()
    {
        $this->hostsFile = config('virtualhosts.hosts_file');
    }

    public function addEntry(string $serverName, string $ip = '127.0.0.1'): bool
    {
        try {
            $content = File::get($this->hostsFile);
            $pattern = '/\b'.preg_quote($serverName, '/').'\b/';

            if (preg_match($pattern, $content)) {
                return true;
            }

            $content = rtrim($content)."\n{$ip}       {$serverName}\n";
            File::put($this->hostsFile, $content);

            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException(
                __('Permissão negada ao escrever no arquivo hosts (:path).', ['path' => $this->hostsFile]) . "\n" .
                __('Para resolver, execute como Administrador:') . "\n" .
                "  1. " . __('Abra o PowerShell ou CMD como Administrador') . "\n" .
                "  2. " . __('Navegue até D:\\www\\localserver') . "\n" .
                "  3. " . __('Execute: fix-permissions.bat') . "\n" .
                __('Ou execute o php artisan serve como Administrador.')
            );
        }
    }

    public function removeEntry(string $serverName): bool
    {
        try {
            $content = File::get($this->hostsFile);
            $lines = explode("\n", $content);
            $pattern = '/\b'.preg_quote($serverName, '/').'\b/';
            $lines = array_filter($lines, fn ($line) => ! preg_match($pattern, $line));
            File::put($this->hostsFile, implode("\n", $lines));

            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException(
                __('Permissão negada ao escrever no arquivo hosts (:path).', ['path' => $this->hostsFile]) . "\n" .
                __('Execute o fix-permissions.bat como Administrador.')
            );
        }
    }

    public function entryExists(string $serverName): bool
    {
        $content = File::get($this->hostsFile);
        $pattern = '/\b'.preg_quote($serverName, '/').'\b/';

        return (bool) preg_match($pattern, $content);
    }
}
