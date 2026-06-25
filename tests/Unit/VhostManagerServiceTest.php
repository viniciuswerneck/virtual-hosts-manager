<?php

namespace Tests\Unit;

use App\Models\VirtualHost;
use App\Services\ApacheService;
use App\Services\HostsFileService;
use App\Services\MkcertService;
use App\Services\VhostManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VhostManagerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('virtualhosts.apache_vhosts_file', 'C:/Apache24/conf/extra/httpd-vhosts.conf');
        config()->set('virtualhosts.apache_bin', 'C:/Apache24/bin/httpd.exe');
        config()->set('virtualhosts.apache_service', 'Apache2.4');
        config()->set('virtualhosts.mkcert_dir', 'C:/mkcert');
        config()->set('app.admin_password', '');
    }

    public function test_apply_apache_config_writes_and_restarts()
    {
        VirtualHost::factory()->create(['server_name' => 'test.local', 'document_root' => 'D:/www/test']);

        $this->mock(ApacheService::class, function ($mock) {
            $mock->shouldReceive('getVhostsFile')->once()->andReturn('C:/Apache24/conf/extra/httpd-vhosts.conf');
            $mock->shouldReceive('writeConfig')->once();
            $mock->shouldReceive('testConfig')->once()->andReturn(['success' => true, 'output' => '']);
            $mock->shouldReceive('restart')->once()->andReturn(['success' => true, 'output' => 'OK']);
        });

        $service = app(VhostManagerService::class);
        $result = $service->applyApacheConfig();

        $this->assertEquals('success', $result['type']);
    }

    public function test_apply_apache_config_returns_warning_on_restart_failure()
    {
        VirtualHost::factory()->create(['server_name' => 'test.local', 'document_root' => 'D:/www/test']);

        $this->mock(ApacheService::class, function ($mock) {
            $mock->shouldReceive('getVhostsFile')->once()->andReturn('C:/Apache24/conf/extra/httpd-vhosts.conf');
            $mock->shouldReceive('writeConfig')->once();
            $mock->shouldReceive('testConfig')->once()->andReturn(['success' => true, 'output' => '']);
            $mock->shouldReceive('restart')->once()->andReturn(['success' => false, 'output' => 'Acesso negado']);
        });

        $service = app(VhostManagerService::class);
        $result = $service->applyApacheConfig();

        $this->assertEquals('warning', $result['type']);
        $this->assertStringContainsString('reiniciado manualmente', $result['message']);
    }

    public function test_apply_apache_config_returns_warning_on_config_error()
    {
        VirtualHost::factory()->create(['server_name' => 'test.local', 'document_root' => 'D:/www/test']);

        $this->mock(ApacheService::class, function ($mock) {
            $mock->shouldReceive('getVhostsFile')->once()->andReturn('C:/Apache24/conf/extra/httpd-vhosts.conf');
            $mock->shouldReceive('writeConfig')->once();
            $mock->shouldReceive('testConfig')->once()->andReturn(['success' => false, 'output' => 'Syntax error']);
            $mock->shouldReceive('restart')->never();
        });

        $service = app(VhostManagerService::class);
        $result = $service->applyApacheConfig();

        $this->assertEquals('warning', $result['type']);
        $this->assertStringContainsString('Syntax error', $result['message']);
    }

    public function test_apply_apache_config_does_not_throw_on_ssl_error()
    {
        VirtualHost::factory()->create(['server_name' => 'test.local', 'document_root' => 'D:/www/test']);

        $this->mock(ApacheService::class, function ($mock) {
            $mock->shouldReceive('getVhostsFile')->once()->andReturn('C:/Apache24/conf/extra/httpd-vhosts.conf');
            $mock->shouldReceive('writeConfig')->once();
            $mock->shouldReceive('testConfig')->once()->andReturn(['success' => false, 'output' => 'AH00141']);
            $mock->shouldReceive('restart')->once()->andReturn(['success' => true, 'output' => 'OK']);
        });

        $service = app(VhostManagerService::class);
        $result = $service->applyApacheConfig();

        $this->assertEquals('success', $result['type']);
    }

    public function test_sync_from_apache_imports_vhosts()
    {
        $this->mock(ApacheService::class, function ($mock) {
            $mock->shouldReceive('parseExisting')->once()->andReturn([
                ['server_name' => 'site1.local', 'document_root' => 'D:/www/site1', 'port' => 80, 'ssl_enabled' => false],
                ['server_name' => 'site2.local', 'document_root' => 'D:/www/site2', 'port' => 443, 'ssl_enabled' => true],
            ]);
        });

        $service = app(VhostManagerService::class);
        $count = $service->syncFromApache();

        $this->assertEquals(2, $count);
        $this->assertDatabaseHas('virtual_hosts', ['server_name' => 'site1.local']);
        $this->assertDatabaseHas('virtual_hosts', ['server_name' => 'site2.local']);
    }

    public function test_sync_from_apache_does_not_duplicate_existing()
    {
        VirtualHost::factory()->create(['server_name' => 'site1.local']);

        $this->mock(ApacheService::class, function ($mock) {
            $mock->shouldReceive('parseExisting')->once()->andReturn([
                ['server_name' => 'site1.local', 'document_root' => 'D:/www/site1', 'port' => 80, 'ssl_enabled' => false],
            ]);
        });

        $service = app(VhostManagerService::class);
        $service->syncFromApache();

        $this->assertDatabaseCount('virtual_hosts', 1);
    }

    public function test_regenerate_cert_deletes_and_generates()
    {
        $vhost = VirtualHost::factory()->create(['server_name' => 'test.local']);

        $this->mock(MkcertService::class, function ($mock) {
            $mock->shouldReceive('delete')->with('test.local')->once();
            $mock->shouldReceive('generate')->with('test.local')->once()->andReturn(['success' => true, 'output' => '']);
        });

        $service = app(VhostManagerService::class);
        $result = $service->regenerateCert($vhost);

        $this->assertTrue($result['success']);
    }
}
