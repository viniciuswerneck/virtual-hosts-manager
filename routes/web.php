<?php

use App\Http\Controllers\VirtualHostController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('virtual-hosts.index');
});

Route::resource('virtual-hosts', VirtualHostController::class)->names([
    'index' => 'virtual-hosts.index',
    'create' => 'virtual-hosts.create',
    'store' => 'virtual-hosts.store',
    'edit' => 'virtual-hosts.edit',
    'update' => 'virtual-hosts.update',
    'destroy' => 'virtual-hosts.destroy',
]);

Route::get('/sync-apache', [VirtualHostController::class, 'sync'])->name('virtual-hosts.sync');
Route::post('/restart-apache', [VirtualHostController::class, 'restartApache'])->name('virtual-hosts.restart');
Route::post('/virtual-hosts/{virtual_host}/regenerate-cert', [VirtualHostController::class, 'regenerateCert'])->name('virtual-hosts.regenerate-cert');
