<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Throwable;

class IniFileService
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function fileExists(): bool
    {
        return File::exists($this->filePath);
    }

    public function readLines(): array
    {
        if (! $this->fileExists()) {
            throw new \RuntimeException("Arquivo não encontrado: {$this->filePath}");
        }

        $content = File::get($this->filePath);

        return explode("\n", str_replace(["\r\n", "\r"], "\n", $content));
    }

    public function writeLines(array $lines): void
    {
        try {
            File::put($this->filePath, implode("\n", $lines));
        } catch (Throwable $e) {
            throw new \RuntimeException(
                "Permissão negada ao escrever em {$this->filePath}."
            );
        }
    }

    public function findDirectiveLine(array $lines, string $key): ?int
    {
        $pattern = '/^\s*;?\s*'.preg_quote($key, '/').'\s*=/i';

        foreach ($lines as $i => $line) {
            if (preg_match($pattern, $line)) {
                return $i;
            }
        }

        return null;
    }

    public function getValue(array $lines, string $key): ?string
    {
        $idx = $this->findDirectiveLine($lines, $key);

        if ($idx === null) {
            return null;
        }

        if (preg_match('/^\s*;?\s*'.preg_quote($key, '/').'\s*=\s*(.*?)\s*$/i', $lines[$idx], $m)) {
            return trim($m[1], '"\'');
        }

        return null;
    }

    public function isEnabled(array $lines, string $key): bool
    {
        $idx = $this->findDirectiveLine($lines, $key);

        if ($idx === null) {
            return false;
        }

        return ! preg_match('/^\s*;/', $lines[$idx]);
    }

    public function setValue(array &$lines, string $key, ?string $value, ?bool $enabled = null): void
    {
        $idx = $this->findDirectiveLine($lines, $key);

        if ($value === null && $idx === null) {
            return;
        }

        if ($value === null) {
            $this->toggle($lines, $key, $enabled ?? true);

            return;
        }

        $needsQuote = str_contains($value, ' ') || str_contains($value, ',');
        $q = $needsQuote ? '"' : '';
        $formatted = $key.' = '.$q.$value.$q;

        if ($enabled === false) {
            $formatted = '; '.$formatted;
        } elseif ($enabled !== true && $idx !== null && preg_match('/^\s*;/', $lines[$idx])) {
            $formatted = '; '.$formatted;
        }

        if ($idx !== null) {
            $lines[$idx] = $formatted;
        } else {
            $lines[] = $formatted;
        }
    }

    public function toggle(array &$lines, string $key, bool $enable): void
    {
        $idx = $this->findDirectiveLine($lines, $key);

        if ($idx === null) {
            return;
        }

        if ($enable) {
            $lines[$idx] = preg_replace('/^\s*;\s*/', '', $lines[$idx], 1);
        } elseif (! preg_match('/^\s*;/', $lines[$idx])) {
            $lines[$idx] = '; '.$lines[$idx];
        }
    }

    public function findAllMatching(array $lines, string $key): array
    {
        $result = [];
        $pattern = '/^\s*;?\s*'.preg_quote($key, '/').'\s*=\s*(.*?)\s*$/i';

        foreach ($lines as $i => $line) {
            if (preg_match($pattern, $line, $m)) {
                $result[] = [
                    'line' => $i,
                    'value' => trim($m[1], '"\' '),
                    'enabled' => ! preg_match('/^\s*;/', $line),
                    'raw' => $line,
                ];
            }
        }

        return $result;
    }
}
