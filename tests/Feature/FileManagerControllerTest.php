<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FileManagerControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $testRoot;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.admin_password', '');

        $this->testRoot = storage_path('app/test-filemanager');
        config()->set('virtualhosts.default_document_root', $this->testRoot);
        File::makeDirectory($this->testRoot, 0755, true, true);
        File::put($this->testRoot.'/test.txt', 'hello');
        File::makeDirectory($this->testRoot.'/subdir', 0755, true, true);
        File::put($this->testRoot.'/subdir/nested.txt', 'nested');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->testRoot);
        parent::tearDown();
    }

    public function test_index_returns_successful_response()
    {
        $response = $this->get(route('file-manager.index'));

        $response->assertStatus(200);
    }

    public function test_index_shows_directory_contents()
    {
        $response = $this->get(route('file-manager.index'));

        $response->assertStatus(200);
        $response->assertSee('test.txt');
        $response->assertSee('subdir');
    }

    public function test_index_navigates_to_subdirectory()
    {
        $response = $this->get(route('file-manager.index', ['path' => $this->testRoot.'/subdir']));

        $response->assertStatus(200);
        $response->assertSee('nested.txt');
    }

    public function test_index_blocks_path_outside_root()
    {
        $response = $this->get(route('file-manager.index', ['path' => 'C:/Windows/System32']));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_index_blocks_parent_directory_escape()
    {
        $response = $this->get(route('file-manager.index', ['path' => $this->testRoot.'/../outside']));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_show_returns_file_content()
    {
        $response = $this->get(route('file-manager.show', ['file' => $this->testRoot.'/test.txt']));

        $response->assertStatus(200);
        $response->assertSee('hello');
    }

    public function test_show_blocks_path_outside_root()
    {
        $response = $this->get(route('file-manager.show', ['file' => 'C:/Windows/System32/drivers/etc/hosts']));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_show_redirects_for_nonexistent_file()
    {
        $response = $this->get(route('file-manager.show', ['file' => $this->testRoot.'/nope.txt']));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_show_redirects_for_missing_file_parameter()
    {
        $response = $this->get(route('file-manager.show'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
