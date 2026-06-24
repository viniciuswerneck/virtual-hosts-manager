<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class MkcertService
{
    private string $mkcertBin;
    private string $certDir;

    public function __construct()
    {
        $this->mkcertBin = 'C:/mkcert/mkcert.exe';
        $this->certDir = 'C:/mkcert';
    }

    public function certExists(string $serverName): bool
    {
        return File::exists("{$this->certDir}/{$serverName}.pem");
    }

    public function generate(string $serverName): array
    {
        $output = [];
        $returnVar = 0;
        $cmd = "\"{$this->mkcertBin}\" -cert-file \"{$this->certDir}/{$serverName}.pem\" -key-file \"{$this->certDir}/{$serverName}-key.pem\" {$serverName} 2>&1";
        exec($cmd, $output, $returnVar);
        return [
            'success' => $returnVar === 0,
            'output' => implode("\n", $output),
        ];
    }

    public function delete(string $serverName): void
    {
        $certFile = "{$this->certDir}/{$serverName}.pem";
        $keyFile = "{$this->certDir}/{$serverName}-key.pem";

        if (File::exists($certFile)) {
            File::delete($certFile);
        }
        if (File::exists($keyFile)) {
            File::delete($keyFile);
        }
    }
}
