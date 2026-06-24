<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            $settings = Setting::pluck('value', 'key')->toArray();

            foreach ($settings as $key => $value) {
                if (config()->has("virtualhosts.{$key}")) {
                    config()->set("virtualhosts.{$key}", $value);
                }
            }
        } catch (\Throwable $e) {
            //
        }
    }
}
