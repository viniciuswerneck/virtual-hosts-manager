<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Throwable;

class HostsFileService
{
    private string $hostsFile;

    public function __construct()
    {
        $this->hostsFile = 'C:/Windows/System32/drivers/etc/hosts';
    }

    public function addEntry(string $serverName): bool
    {
        try {
            $content = File::get($this->hostsFile);

            if (str_contains($content, $serverName)) {
                return true;
            }

            $content = rtrim($content) . "\n127.0.0.1       {$serverName}\n";
            File::put($this->hostsFile, $content);
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException(
                "Permissão negada ao escrever no arquivo hosts ({$this->hostsFile}).\n" .
                "Para resolver, execute como Administrador:\n" .
                "  1. Abra o PowerShell ou CMD como Administrador\n" .
                "  2. Navegue até D:\\www\\localserver\n" .
                "  3. Execute: fix-permissions.bat\n" .
                "Ou execute o php artisan serve como Administrador."
            );
        }
    }

    public function removeEntry(string $serverName): bool
    {
        try {
            $content = File::get($this->hostsFile);
            $lines = explode("\n", $content);
            $lines = array_filter($lines, fn($line) => !str_contains($line, $serverName));
            File::put($this->hostsFile, implode("\n", $lines));
            return true;
        } catch (Throwable $e) {
            throw new \RuntimeException(
                "Permissão negada ao escrever no arquivo hosts ({$this->hostsFile}).\n" .
                "Execute o fix-permissions.bat como Administrador."
            );
        }
    }

    public function entryExists(string $serverName): bool
    {
        $content = File::get($this->hostsFile);
        return str_contains($content, $serverName);
    }
}
