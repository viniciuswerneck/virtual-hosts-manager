<?php

namespace App\Http\Controllers;

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

        $certDir = config('virtualhosts.mkcert_dir');
        $sslWithoutCert = VirtualHost::where('ssl_enabled', true)->get()->filter(function ($v) use ($certDir) {
            return !File::exists("{$certDir}/{$v->server_name}.pem");
        });

        $apacheVhosts = $apache->parseExisting();
        $apacheNames = array_column($apacheVhosts, 'server_name');
        $notInApache = VirtualHost::where('active', true)->get()->filter(function ($v) use ($apacheNames) {
            return !in_array($v->server_name, $apacheNames);
        });

        return view('dashboard.index', compact(
            'totalVhosts', 'activeVhosts', 'sslCount', 'inactiveCount',
            'apacheOnline', 'recentVhosts', 'sslWithoutCert', 'notInApache'
        ));
    }
}
