<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.admin_password', '');
    }

    public function test_index_returns_all_config_keys()
    {
        Setting::create(['key' => 'apache_bin', 'value' => 'D:/custom/httpd.exe']);

        $response = $this->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee('D:/custom/httpd.exe');
    }

    public function test_index_uses_default_when_setting_not_in_db()
    {
        $response = $this->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee(config('virtualhosts.apache_bin'));
    }

    public function test_update_saves_all_keys()
    {
        $response = $this->post(route('settings.update'), [
            'apache_vhosts_file' => 'C:/Apache24/conf/extra/httpd-vhosts.conf',
            'apache_bin' => 'C:/Apache24/bin/httpd.exe',
            'apache_service' => 'Apache2.4',
            'apache_ssl_port' => '443',
            'hosts_file' => 'C:/Windows/System32/drivers/etc/hosts',
            'mkcert_bin' => 'C:/mkcert/mkcert.exe',
            'mkcert_dir' => 'C:/mkcert',
            'default_document_root' => 'D:/www/',
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('settings', ['key' => 'apache_bin', 'value' => 'C:/Apache24/bin/httpd.exe']);
        $this->assertDatabaseHas('settings', ['key' => 'apache_ssl_port', 'value' => '443']);
    }

    public function test_update_only_saves_known_keys()
    {
        $response = $this->post(route('settings.update'), [
            'apache_bin' => 'C:/Apache24/bin/httpd.exe',
            'unknown_key' => 'should_not_be_saved',
        ]);

        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseMissing('settings', ['key' => 'unknown_key']);
    }

    public function test_update_overwrites_existing_setting()
    {
        Setting::create(['key' => 'apache_bin', 'value' => 'old_value']);

        $this->post(route('settings.update'), [
            'apache_bin' => 'new_value',
        ]);

        $this->assertDatabaseHas('settings', ['key' => 'apache_bin', 'value' => 'new_value']);
        $this->assertDatabaseCount('settings', 1);
    }
}
