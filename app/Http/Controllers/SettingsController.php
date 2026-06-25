<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        $config = [
            'apache_vhosts_file' => config('virtualhosts.apache_vhosts_file'),
            'apache_bin' => config('virtualhosts.apache_bin'),
            'apache_service' => config('virtualhosts.apache_service'),
            'hosts_file' => config('virtualhosts.hosts_file'),
            'mkcert_bin' => config('virtualhosts.mkcert_bin'),
            'mkcert_dir' => config('virtualhosts.mkcert_dir'),
            'default_document_root' => config('virtualhosts.default_document_root'),
        ];

        foreach ($config as $key => $value) {
            if (isset($settings[$key])) {
                $config[$key] = $settings[$key];
            }
        }

        return view('settings.index', compact('config'));
    }

    public function update(Request $request)
    {
        $keys = [
            'apache_vhosts_file', 'apache_bin', 'apache_service',
            'hosts_file', 'mkcert_bin', 'mkcert_dir', 'default_document_root',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $request->input($key)]
                );
            }
        }

        return redirect()->route('settings.index')
            ->with('success', 'Configurações salvas com sucesso!');
    }
}
