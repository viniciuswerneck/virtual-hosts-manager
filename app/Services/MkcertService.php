<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class MkcertService
{
    private string $mkcertBin;
    private string $certDir;
    private string $caRoot;

    public function __construct()
    {
        $this->mkcertBin = config('virtualhosts.mkcert_bin');
        $this->certDir = config('virtualhosts.mkcert_dir');
        $this->caRoot = config('virtualhosts.mkcert_caroot', storage_path('app/mkcert'));
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
        $process->setEnv(['CAROOT' => $this->caRoot]);
        $process->setTimeout(30);
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
