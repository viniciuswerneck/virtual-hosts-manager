<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PortScannerService
{
    public function getOccupiedPorts(): array
    {
        return Cache::remember('occupied_ports', 60, function (): array {
            return $this->scanPorts();
        });
    }

    public function isPortAvailable(int $port): bool
    {
        $occupied = $this->getOccupiedPorts();

        return ! isset($occupied[$port]);
    }

    public function getPortInfo(int $port): ?array
    {
        $occupied = $this->getOccupiedPorts();

        return $occupied[$port] ?? null;
    }

    private function scanPorts(): array
    {
        $ports = [];
        $output = [];

        $result = @exec('netstat -ano -p tcp 2>&1', $output);
        if ($result === false) {
            return [];
        }

        foreach ($output as $line) {
            if (! preg_match('/^  TCP\s+\S+:(\d+)\s+\S+\s+LISTENING\s+(\d+)$/i', trim($line), $m)) {
                continue;
            }

            $port = (int) $m[1];
            $pid = (int) $m[2];
            $processName = $this->getProcessName($pid);

            $ports[$port] = [
                'port' => $port,
                'pid' => $pid,
                'process' => $processName,
            ];
        }

        return $ports;
    }

    private function getProcessName(int $pid): string
    {
        if ($pid === 0) {
            return 'System';
        }

        $output = [];
        @exec("tasklist /FI \"PID eq {$pid}\" /FO CSV /NH 2>&1", $output);

        foreach ($output as $line) {
            if (preg_match('/^"([^"]+)"/', $line, $m)) {
                return $m[1];
            }
        }

        return 'Unknown';
    }
}
