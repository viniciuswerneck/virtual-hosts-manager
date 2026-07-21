<?php

declare(strict_types=1);

namespace App\Services;

class DnsTestService
{
    public function testResolution(string $hostname): array
    {
        $output = [];
        $result = @exec("nslookup {$hostname} 2>&1", $output);
        $raw = implode("\n", $output);

        $ip = null;
        $resolved = false;

        foreach ($output as $line) {
            if (preg_match('/^Address:\s*(\d+\.\d+\.\d+\.\d+)$/i', trim($line), $m)) {
                $ip = $m[1];
                $resolved = true;
            }
        }

        $pingTime = $this->ping($hostname);

        return [
            'hostname' => $hostname,
            'resolved' => $resolved,
            'ip' => $ip,
            'ping_ms' => $pingTime,
            'raw' => $raw,
        ];
    }

    private function ping(string $host): ?int
    {
        $output = [];
        @exec("ping -n 1 -w 2000 {$host} 2>&1", $output);
        $raw = implode("\n", $output);

        if (preg_match('/TTL=(\d+)/i', $raw, $m)) {
            return (int) $m[1];
        }

        return null;
    }
}
