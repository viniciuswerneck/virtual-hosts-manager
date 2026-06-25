<?php

namespace Tests\Unit;

use App\Services\HostsFileService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HostsFileServiceTest extends TestCase
{
    private HostsFileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('virtualhosts.hosts_file', 'C:/Windows/System32/drivers/etc/hosts');
        $this->service = app(HostsFileService::class);
    }

    public function test_add_entry_appends_to_hosts()
    {
        File::shouldReceive('get')
            ->with('C:/Windows/System32/drivers/etc/hosts')
            ->once()
            ->andReturn("127.0.0.1       localhost\n");

        File::shouldReceive('put')
            ->with('C:/Windows/System32/drivers/etc/hosts', "127.0.0.1       localhost\n127.0.0.1       meusite.local\n")
            ->once();

        $this->assertTrue($this->service->addEntry('meusite.local'));
    }

    public function test_add_entry_skips_if_already_exists()
    {
        File::shouldReceive('get')
            ->with('C:/Windows/System32/drivers/etc/hosts')
            ->once()
            ->andReturn("127.0.0.1       meusite.local\n");

        File::shouldReceive('put')->never();

        $this->assertTrue($this->service->addEntry('meusite.local'));
    }

    public function test_remove_entry_removes_matching_line()
    {
        File::shouldReceive('get')
            ->with('C:/Windows/System32/drivers/etc/hosts')
            ->once()
            ->andReturn("127.0.0.1       localhost\n127.0.0.1       meusite.local\n");

        File::shouldReceive('put')
            ->with('C:/Windows/System32/drivers/etc/hosts', "127.0.0.1       localhost\n")
            ->once();

        $this->assertTrue($this->service->removeEntry('meusite.local'));
    }

    public function test_entry_exists_returns_true_when_found()
    {
        File::shouldReceive('get')
            ->with('C:/Windows/System32/drivers/etc/hosts')
            ->once()
            ->andReturn("127.0.0.1       meusite.local\n");

        $this->assertTrue($this->service->entryExists('meusite.local'));
    }

    public function test_entry_exists_returns_false_when_not_found()
    {
        File::shouldReceive('get')
            ->with('C:/Windows/System32/drivers/etc/hosts')
            ->once()
            ->andReturn("127.0.0.1       localhost\n");

        $this->assertFalse($this->service->entryExists('meusite.local'));
    }
}
