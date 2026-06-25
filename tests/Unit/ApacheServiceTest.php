<?php

namespace Tests\Unit;

use App\Services\ApacheService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ApacheServiceTest extends TestCase
{
    private ApacheService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('virtualhosts.apache_vhosts_file', 'C:/Apache24/conf/extra/httpd-vhosts.conf');
        config()->set('virtualhosts.apache_bin', 'C:/Apache24/bin/httpd.exe');
        config()->set('virtualhosts.mkcert_dir', 'C:/mkcert');
        $this->service = app(ApacheService::class);
    }

    public function test_parse_existing_returns_empty_when_file_missing()
    {
        File::shouldReceive('exists')
            ->with('C:/Apache24/conf/extra/httpd-vhosts.conf')
            ->once()
            ->andReturn(false);

        $this->assertEmpty($this->service->parseExisting());
    }

    public function test_parse_existing_extracts_vhosts()
    {
        $content = <<<'VHOSTS'
<VirtualHost *:80>
    ServerName meusite.local
    DocumentRoot "D:/www/meusite"
</VirtualHost>
VHOSTS;

        File::shouldReceive('exists')
            ->with('C:/Apache24/conf/extra/httpd-vhosts.conf')
            ->once()
            ->andReturn(true);

        File::shouldReceive('get')
            ->with('C:/Apache24/conf/extra/httpd-vhosts.conf')
            ->once()
            ->andReturn($content);

        $result = $this->service->parseExisting();

        $this->assertCount(1, $result);
        $this->assertEquals('meusite.local', $result[0]['server_name']);
        $this->assertEquals('D:/www/meusite', $result[0]['document_root']);
        $this->assertEquals(80, $result[0]['port']);
        $this->assertFalse($result[0]['ssl_enabled']);
    }

    public function test_parse_existing_detects_ssl()
    {
        $content = <<<'VHOSTS'
<VirtualHost *:80>
    ServerName meusite.local
    DocumentRoot "D:/www/meusite"
</VirtualHost>
<VirtualHost *:443>
    ServerName meusite.local
    DocumentRoot "D:/www/meusite"
    SSLEngine on
</VirtualHost>
VHOSTS;

        File::shouldReceive('exists')
            ->andReturn(true);
        File::shouldReceive('get')
            ->andReturn($content);

        $result = $this->service->parseExisting();

        $this->assertCount(1, $result);
        $this->assertTrue($result[0]['ssl_enabled']);
    }

    public function test_write_config_throws_on_permission_error()
    {
        $vhosts = [
            ['server_name' => 'test.local', 'document_root' => 'D:/www/test', 'port' => 80, 'ssl_enabled' => false],
        ];

        File::shouldReceive('put')
            ->andThrow(new \Exception('Permission denied'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Permissão negada');

        $this->service->writeConfig($vhosts);
    }

    public function test_get_vhosts_file()
    {
        $this->assertEquals(
            'C:/Apache24/conf/extra/httpd-vhosts.conf',
            $this->service->getVhostsFile()
        );
    }
}
