<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class MkcertService
{
    private string $mkcertBin;
    private string $certDir;

    public function __construct()
    {
        $this->mkcertBin = config('virtualhosts.mkcert_bin');
        $this->certDir = config('virtualhosts.mkcert_dir');
    }

    public function certExists(string $serverName): bool
    {
        return File::exists("{$this->certDir}/{$serverName}.pem");
    }

    public function generate(string $serverName): array
    {
        $process = new Process([
            $this->mkcertBin,
            '-cert-file', "{$this->certDir}/{$serverName}.pem",
            '-key-file', "{$this->certDir}/{$serverName}-key.pem",
            $serverName,
        ]);
        $process->run();

        return [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput() ?: $process->getErrorOutput(),
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
