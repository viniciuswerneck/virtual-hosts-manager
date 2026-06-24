<?php

namespace Tests\Feature;

use App\Models\VirtualHost;
use App\Services\ApacheService;
use App\Services\HostsFileService;
use App\Services\MkcertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VirtualHostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.admin_password', '');
    }

    public function test_index_returns_successful_response()
    {
        VirtualHost::factory()->count(3)->create();

        $response = $this->get(route('virtual-hosts.index'));

        $response->assertStatus(200);
    }

    public function test_create_returns_successful_response()
    {
        $response = $this->get(route('virtual-hosts.create'));

        $response->assertStatus(200);
    }

    public function test_show_returns_successful_response()
    {
        $vhost = VirtualHost::factory()->create();

        $response = $this->get(route('virtual-hosts.show', $vhost));

        $response->assertStatus(200);
    }

    public function test_edit_returns_successful_response()
    {
        $vhost = VirtualHost::factory()->create();

        $response = $this->get(route('virtual-hosts.edit', $vhost));

        $response->assertStatus(200);
    }

    public function test_store_validates_required_fields()
    {
        $response = $this->post(route('virtual-hosts.store'), []);

        $response->assertSessionHasErrors(['server_name', 'document_root']);
    }

    public function test_store_validates_server_name_format()
    {
        $response = $this->post(route('virtual-hosts.store'), [
            'server_name' => 'invalid server name!',
            'document_root' => 'D:/www/test',
        ]);

        $response->assertSessionHasErrors(['server_name']);
    }

    public function test_store_creates_vhost_and_redirects()
    {
        $this->mock(HostsFileService::class, function ($mock) {
            $mock->shouldReceive('addEntry')->once()->andReturn(true);
        });

        $this->mock(MkcertService::class, function ($mock) {
            $mock->shouldReceive('certExists')->once()->andReturn(false);
            $mock->shouldReceive('generate')->once()->andReturn(['success' => true, 'output' => '']);
        });

        $this->mock(ApacheService::class, function ($mock) {
            $mock->shouldReceive('writeConfig')->once();
            $mock->shouldReceive('testConfig')->once()->andReturn(['success' => true, 'output' => '']);
            $mock->shouldReceive('restart')->once()->andReturn(['success' => true, 'output' => '']);
        });

        $response = $this->post(route('virtual-hosts.store'), [
            'server_name' => 'meusite.local',
            'document_root' => 'D:/www/meusite',
            'port' => 80,
            'ssl_enabled' => true,
        ]);

        $response->assertRedirect(route('virtual-hosts.index'));
        $this->assertDatabaseHas('virtual_hosts', ['server_name' => 'meusite.local']);
    }

    public function test_destroy_removes_vhost_and_redirects()
    {
        $vhost = VirtualHost::factory()->create();

        $this->mock(HostsFileService::class, function ($mock) {
            $mock->shouldReceive('removeEntry')->once()->andReturn(true);
        });

        $this->mock(MkcertService::class, function ($mock) {
            $mock->shouldReceive('delete')->once();
        });

        $this->mock(ApacheService::class, function ($mock) {
            $mock->shouldReceive('writeConfig')->once();
            $mock->shouldReceive('testConfig')->once()->andReturn(['success' => true, 'output' => '']);
            $mock->shouldReceive('restart')->once()->andReturn(['success' => true, 'output' => '']);
        });

        $response = $this->delete(route('virtual-hosts.destroy', $vhost));

        $response->assertRedirect(route('virtual-hosts.index'));
        $this->assertDatabaseMissing('virtual_hosts', ['server_name' => $vhost->server_name]);
    }
}
