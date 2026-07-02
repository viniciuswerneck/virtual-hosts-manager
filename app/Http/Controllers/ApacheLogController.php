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
        $lines = $request->input('lines', 100);

        $logContent = '';

        if ($logPath && File::exists($logPath)) {
            $logContent = $this->readLog($logPath, $level, (int) $lines);
        }

        $levels = ['all', 'error', 'warn', 'notice', 'info', 'debug'];

        return view('logs.index', compact('logContent', 'level', 'lines', 'levels', 'logPath'));
    }

    public function stream(Request $request)
    {
        $logPath = config('virtualhosts.apache_error_log');
        $level = $request->input('level', 'all');
        $lines = $request->input('lines', 100);

        if (!$logPath || !File::exists($logPath)) {
            return response()->json(['content' => '', 'error' => 'Log file not found']);
        }

        $content = $this->readLog($logPath, $level, (int) $lines);

        return response()->json(['content' => $content]);
    }

    private function readLog(string $logPath, string $level, int $lines): string
    {
        $content = File::get($logPath);
        $allLines = explode("\n", $content);
        $allLines = array_filter($allLines);
        $allLines = array_reverse($allLines);

        if ($level !== 'all') {
            $levelMap = [
                'error' => '[error]',
                'warn' => '[warn]',
                'notice' => '[notice]',
                'info' => '[info]',
                'debug' => '[debug]',
            ];
            $search = $levelMap[$level] ?? $level;
            $allLines = array_filter($allLines, fn($line) => str_contains($line, $search));
        }

        $allLines = array_slice($allLines, 0, $lines);

        return implode("\n", $allLines);
    }
}
