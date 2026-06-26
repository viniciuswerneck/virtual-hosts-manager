<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_access_when_password_empty()
    {
        config()->set('app.admin_password', '');

        $response = $this->get(route('virtual-hosts.index'));

        $response->assertStatus(200);
    }

    public function test_redirects_to_login_when_not_authenticated()
    {
        config()->set('app.admin_password', '$2y$12$hashedpassword');

        $response = $this->get(route('virtual-hosts.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_allows_access_to_login_page_without_auth()
    {
        config()->set('app.admin_password', '$2y$12$hashedpassword');

        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
    }

    public function test_login_with_correct_password()
    {
        $plain = 'admin123';
        $hash = password_hash($plain, PASSWORD_BCRYPT);
        config()->set('app.admin_password', $hash);

        $response = $this->post(route('admin.login.post'), [
            'password' => $plain,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(session()->get('admin_authenticated'));
    }

    public function test_login_with_incorrect_password()
    {
        config()->set('app.admin_password', password_hash('admin123', PASSWORD_BCRYPT));

        $response = $this->post(route('admin.login.post'), [
            'password' => 'wrong_password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_logout_clears_session()
    {
        config()->set('app.admin_password', password_hash('admin123', PASSWORD_BCRYPT));

        $this->post(route('admin.login.post'), ['password' => 'admin123']);
        $this->assertTrue(session()->get('admin_authenticated'));

        $response = $this->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertNull(session()->get('admin_authenticated'));
    }

    public function test_authenticated_user_can_access_protected_pages()
    {
        config()->set('app.admin_password', password_hash('admin123', PASSWORD_BCRYPT));

        $this->post(route('admin.login.post'), ['password' => 'admin123']);

        $response = $this->get(route('virtual-hosts.index'));

        $response->assertStatus(200);
    }

    public function test_login_page_redirects_authenticated_user()
    {
        config()->set('app.admin_password', password_hash('admin123', PASSWORD_BCRYPT));

        $this->post(route('admin.login.post'), ['password' => 'admin123']);

        $response = $this->get(route('admin.login'));

        $response->assertRedirect(route('dashboard'));
    }
}
