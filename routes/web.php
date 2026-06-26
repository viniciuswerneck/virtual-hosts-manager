<?php

use App\Http\Controllers\ApacheLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VirtualHostController;
use Illuminate\Support\Facades\Route;

Route::post('/admin/logout', function (\Illuminate\Http\Request $request) {
    $request->session()->forget('admin_authenticated');
    return redirect()->route('admin.login');
})->name('admin.logout');

Route::get('/admin/login', function () {
    if (session('admin_authenticated')) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
})->name('admin.login');

Route::post('/admin/login', function (\Illuminate\Http\Request $request) {
    $password = config('app.admin_password');

    if (empty($password)) {
        return redirect()->route('dashboard');
    }

    if (\Illuminate\Support\Facades\Hash::check($request->input('password'), $password)) {
        $request->session()->put('admin_authenticated', true);
        return redirect()->intended(route('dashboard'));
    }

    return back()->with('error', 'Senha incorreta.');
})->name('admin.login.post')->middleware('throttle:5,1');

Route::middleware(['admin.auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('virtual-hosts', VirtualHostController::class)->names([
        'index' => 'virtual-hosts.index',
        'create' => 'virtual-hosts.create',
        'store' => 'virtual-hosts.store',
        'edit' => 'virtual-hosts.edit',
        'update' => 'virtual-hosts.update',
        'destroy' => 'virtual-hosts.destroy',
    ]);

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/logs', [ApacheLogController::class, 'index'])->name('logs.index');

    Route::get('/sync-apache', [VirtualHostController::class, 'sync'])->name('virtual-hosts.sync');
    Route::post('/restart-apache', [VirtualHostController::class, 'restartApache'])->name('virtual-hosts.restart');
    Route::post('/virtual-hosts/{virtual_host}/toggle', [VirtualHostController::class, 'toggleActive'])->name('virtual-hosts.toggle');
    Route::post('/virtual-hosts/{virtual_host}/regenerate-cert', [VirtualHostController::class, 'regenerateCert'])->name('virtual-hosts.regenerate-cert');
    Route::get('/virtual-hosts/export/json', [VirtualHostController::class, 'exportJson'])->name('virtual-hosts.export');
    Route::post('/virtual-hosts/import/json', [VirtualHostController::class, 'importJson'])->name('virtual-hosts.import');
});
