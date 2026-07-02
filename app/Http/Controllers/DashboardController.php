<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\VirtualHost;
use App\Services\ApacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    public function index(ApacheService $apache)
    {
        $totalVhosts = VirtualHost::count();
        $activeVhosts = VirtualHost::where('active', true)->count();
        $sslCount = VirtualHost::where('ssl_enabled', true)->count();
        $inactiveCount = VirtualHost::where('active', false)->count();

        $apacheOnline = Cache::remember('apache_running', 10, function () use ($apache) {
            try { return $apache->isRunning(); } catch (\Throwable) { return false; }
        });

        $recentVhosts = VirtualHost::orderBy('created_at', 'desc')->take(5)->get();
        $recentActivities = ActivityLog::latest()->take(7)->get();

        $certDir = config('virtualhosts.mkcert_dir');
        $sslWithoutCert = VirtualHost::where('ssl_enabled', true)->get()->filter(function ($v) use ($certDir) {
            return !File::exists("{$certDir}/{$v->server_name}.pem");
        });

        $apacheVhosts = $apache->parseExisting();
        $apacheNames = array_column($apacheVhosts, 'server_name');
        $notInApache = VirtualHost::where('active', true)->get()->filter(function ($v) use ($apacheNames) {
            return !in_array($v->server_name, $apacheNames);
        });

        $apacheConfigTest = $apache->testConfig();
        $apachePidCount = $apacheOnline ? $this->countApacheProcesses() : 0;

        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsed = $diskTotal - $diskFree;
        $diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100) : 0;

        $phpVersion = PHP_VERSION;
        $phpMemoryLimit = ini_get('memory_limit');
        $phpMaxExecTime = ini_get('max_execution_time');
        $phpUploadMax = ini_get('upload_max_filesize');

        $phpVersionTotal = VirtualHost::whereNotNull('php_version')
            ->selectRaw('php_version, count(*) as total')
            ->groupBy('php_version')
            ->orderBy('php_version')
            ->get();
        $phpNullCount = VirtualHost::whereNull('php_version')->count();

        return view('dashboard.index', compact(
            'totalVhosts', 'activeVhosts', 'sslCount', 'inactiveCount',
            'apacheOnline', 'recentVhosts', 'recentActivities', 'sslWithoutCert', 'notInApache',
            'apacheConfigTest', 'apachePidCount',
            'diskFree', 'diskTotal', 'diskUsed', 'diskPercent',
            'phpVersion', 'phpMemoryLimit', 'phpMaxExecTime', 'phpUploadMax',
            'phpVersionTotal', 'phpNullCount',
        ));
    }

    private function countApacheProcesses(): int
    {
        try {
            @exec('tasklist /NH /FI "IMAGENAME eq httpd.exe" 2>&1', $output, $exitCode);
            if ($exitCode !== 0) {
                return 0;
            }
            $count = 0;
            foreach ($output as $line) {
                if (stripos($line, 'httpd.exe') !== false) {
                    $count++;
                }
            }
            return $count;
        } catch (\Throwable) {
            return 0;
        }
    }
}
