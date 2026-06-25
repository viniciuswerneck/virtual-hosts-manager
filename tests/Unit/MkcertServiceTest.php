<?php

namespace Tests\Unit;

use App\Services\MkcertService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MkcertServiceTest extends TestCase
{
    private MkcertService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('virtualhosts.mkcert_bin', 'C:/mkcert/mkcert.exe');
        config()->set('virtualhosts.mkcert_dir', 'C:/mkcert');
        $this->service = app(MkcertService::class);
    }

    public function test_cert_exists_returns_false_when_file_missing()
    {
        File::shouldReceive('exists')
            ->with('C:/mkcert/test.local.pem')
            ->once()
            ->andReturn(false);

        $this->assertFalse($this->service->certExists('test.local'));
    }

    public function test_cert_exists_returns_true_when_file_present()
    {
        File::shouldReceive('exists')
            ->with('C:/mkcert/test.local.pem')
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->service->certExists('test.local'));
    }

    public function test_delete_removes_cert_and_key_files()
    {
        File::shouldReceive('exists')
            ->with('C:/mkcert/test.local.pem')
            ->once()
            ->andReturn(true);

        File::shouldReceive('exists')
            ->with('C:/mkcert/test.local-key.pem')
            ->once()
            ->andReturn(true);

        File::shouldReceive('delete')
            ->with('C:/mkcert/test.local.pem')
            ->once();

        File::shouldReceive('delete')
            ->with('C:/mkcert/test.local-key.pem')
            ->once();

        $this->service->delete('test.local');
    }

    public function test_delete_skips_when_files_missing()
    {
        File::shouldReceive('exists')
            ->with('C:/mkcert/test.local.pem')
            ->once()
            ->andReturn(false);

        File::shouldReceive('exists')
            ->with('C:/mkcert/test.local-key.pem')
            ->once()
            ->andReturn(false);

        File::shouldReceive('delete')->never();

        $this->service->delete('test.local');
    }
}
