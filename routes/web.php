<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ApacheLogController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VirtualHostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::post('/admin/logout', function (Request $request) {
    $request->session()->forget('admin_authenticated');

    return redirect()->route('admin.login');
})->name('admin.logout');

Route::get('/admin/login', function () {
    if (session('admin_authenticated')) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
})->name('admin.login');

Route::post('/locale', function (Request $request) {
    $locale = $request->input('locale', 'pt');
    if (in_array($locale, ['en', 'pt', 'es'])) {
        $request->session()->put('locale', $locale);
        app()->setLocale($locale);
    }

    return redirect()->back();
})->name('locale.set');

Route::post('/admin/login', function (Request $request) {
    $password = config('app.admin_password');

    if (empty($password) && ! app()->environment('production')) {
        return redirect()->route('dashboard');
    }

    if (Hash::check($request->input('password'), $password)) {
        $request->session()->put('admin_authenticated', true);

        return redirect()->intended(route('dashboard'));
    }

    Log::warning('Tentativa de login falhou', [
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

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
    Route::get('/logs/stream', [ApacheLogController::class, 'stream'])->name('logs.stream');

    Route::get('/sync-apache', [VirtualHostController::class, 'sync'])->name('virtual-hosts.sync');
    Route::post('/restart-apache', [VirtualHostController::class, 'restartApache'])->name('virtual-hosts.restart');
    Route::post('/virtual-hosts/{virtual_host}/toggle', [VirtualHostController::class, 'toggleActive'])->name('virtual-hosts.toggle');
    Route::post('/virtual-hosts/{virtual_host}/regenerate-cert', [VirtualHostController::class, 'regenerateCert'])->name('virtual-hosts.regenerate-cert');
    Route::get('/virtual-hosts/export/json', [VirtualHostController::class, 'exportJson'])->name('virtual-hosts.export');
    Route::post('/virtual-hosts/import/json', [VirtualHostController::class, 'importJson'])->name('virtual-hosts.import');

    Route::post('/virtual-hosts/batch/toggle', [VirtualHostController::class, 'batchToggle'])->name('virtual-hosts.batch.toggle');
    Route::post('/virtual-hosts/batch/delete', [VirtualHostController::class, 'batchDelete'])->name('virtual-hosts.batch.delete');

    Route::get('/backup/export', [VirtualHostController::class, 'exportFullBackup'])->name('backup.export');
    Route::post('/backup/import', [VirtualHostController::class, 'importFullBackup'])->name('backup.import');

    Route::get('/audit-log', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::get('/files', [FileManagerController::class, 'index'])->name('file-manager.index');
    Route::get('/files/show', [FileManagerController::class, 'show'])->name('file-manager.show');

    Route::get('/config/php', [ConfigController::class, 'phpIndex'])->name('config.php.index');
    Route::post('/config/php', [ConfigController::class, 'phpUpdate'])->name('config.php.update');
    Route::post('/config/php/extension-toggle', [ConfigController::class, 'phpExtensionToggle'])->name('config.php.extension-toggle');

    Route::get('/config/apache', [ConfigController::class, 'apacheIndex'])->name('config.apache.index');
    Route::post('/config/apache', [ConfigController::class, 'apacheUpdate'])->name('config.apache.update');
    Route::post('/config/apache/module-toggle', [ConfigController::class, 'apacheToggleModule'])->name('config.apache.module-toggle');
    Route::post('/config/apache/test', [ConfigController::class, 'apacheTest'])->name('config.apache.test');
    Route::post('/config/apache/restart', [ConfigController::class, 'apacheRestart'])->name('config.apache.restart');
});
