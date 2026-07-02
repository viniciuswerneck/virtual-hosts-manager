<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ApacheLogController extends Controller
{
    public function index(Request $request)
    {
        $logPath = config('virtualhosts.apache_error_log');
        $level = $request->input('level', 'all');
        $search = $request->input('search', '');
        $lines = (int) $request->input('lines', 200);

        $logEntries = [];
        $levelCounts = ['error' => 0, 'warn' => 0, 'notice' => 0, 'info' => 0, 'debug' => 0];
        $modules = [];

        if ($logPath && File::exists($logPath)) {
            $result = $this->parseLog($logPath, $level, $search, $lines);
            $logEntries = $result['entries'];
            $levelCounts = $result['levelCounts'];
            $modules = $result['modules'];
        }

        $levels = ['all', 'error', 'warn', 'notice', 'info', 'debug'];

        return view('logs.index', compact(
            'logEntries', 'level', 'search', 'lines', 'levels', 'logPath',
            'levelCounts', 'modules'
        ));
    }

    public function stream(Request $request)
    {
        $logPath = config('virtualhosts.apache_error_log');
        $level = $request->input('level', 'all');
        $search = $request->input('search', '');
        $lines = (int) $request->input('lines', 200);

        if (!$logPath || !File::exists($logPath)) {
            return response()->json(['entries' => [], 'error' => 'Log file not found']);
        }

        $result = $this->parseLog($logPath, $level, $search, $lines);

        return response()->json([
            'entries' => $result['entries'],
            'levelCounts' => $result['levelCounts'],
        ]);
    }

    private function formatTimestamp(string $raw): array
    {
        $formats = [
            'D M d H:i:s.u Y',
            'D M d H:i:s Y',
            'd/M/Y:H:i:s',
        ];

        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $raw);
            if ($dt) {
                $now = new \DateTime();
                $diff = $now->getTimestamp() - $dt->getTimestamp();

                if ($diff < 300) {
                    $display = 'Agora';
                } elseif ($diff < 3600) {
                    $display = 'Há ' . floor($diff / 60) . 'min';
                } elseif ($dt->format('Y-m-d') === $now->format('Y-m-d')) {
                    $display = 'Hoje ' . $dt->format('H:i');
                } elseif ($dt->format('Y-m-d') === $now->modify('-1 day')->format('Y-m-d')) {
                    $display = 'Ontem ' . $dt->format('H:i');
                } else {
                    $display = $dt->format('d/m/Y H:i');
                }

                return [
                    'display' => $display,
                    'tooltip' => $dt->format('d/m/Y H:i:s'),
                ];
            }
        }

        return ['display' => $raw, 'tooltip' => $raw];
    }

    private function parseLog(string $logPath, string $level, string $search, int $maxLines): array
    {
        $content = File::get($logPath);
        $rawLines = explode("\n", $content);
        $rawLines = array_filter($rawLines);
        $rawLines = array_reverse($rawLines);

        $entries = [];
        $levelCounts = ['error' => 0, 'warn' => 0, 'notice' => 0, 'info' => 0, 'debug' => 0];
        $moduleSet = [];

        $pattern = '/^\[([^\]]+)\]\s+\[([^:]+):([^\]]+)\](?:\s+\[pid\s+([^\]]+)\])?(?:\s+\[client\s+([^\]]+)\])?\s+(.+)$/i';

        foreach ($rawLines as $raw) {
            $trimmed = trim($raw);
            if ($trimmed === '') {
                continue;
            }

            $parsed = null;
            $logLevel = 'info';

            if (preg_match($pattern, $trimmed, $m)) {
                $logLevel = strtolower($m[3]);
                $module = $m[2];
                $moduleSet[$module] = true;

                $tsFormatted = $this->formatTimestamp($m[1]);
                $parsed = [
                    'timestamp' => $m[1],
                    'ts_formatted' => $tsFormatted['display'],
                    'ts_tooltip' => $tsFormatted['tooltip'],
                    'module' => $module,
                    'level' => $logLevel,
                    'pid' => $m[4] ?? '',
                    'client' => $m[5] ?? '',
                    'message' => $m[6],
                    'raw' => $trimmed,
                ];
            } else {
                $parsed = [
                    'timestamp' => '',
                    'ts_formatted' => '',
                    'ts_tooltip' => '',
                    'module' => '',
                    'level' => 'info',
                    'pid' => '',
                    'client' => '',
                    'message' => $trimmed,
                    'raw' => $trimmed,
                ];
            }

            if ($level !== 'all' && $logLevel !== $level) {
                continue;
            }

            if ($search && !str_contains(strtolower($trimmed), strtolower($search))) {
                continue;
            }

            if (isset($levelCounts[$logLevel])) {
                $levelCounts[$logLevel]++;
            } else {
                $levelCounts['info']++;
            }

            $entries[] = $parsed;

            if (count($entries) >= $maxLines) {
                break;
            }
        }

        return [
            'entries' => $entries,
            'levelCounts' => $levelCounts,
            'modules' => array_keys($moduleSet),
        ];
    }
}