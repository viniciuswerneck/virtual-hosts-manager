<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible_without_auth()
    {
        config()->set('app.admin_password', password_hash('secret', PASSWORD_BCRYPT));

        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
    }

    public function test_protected_routes_redirect_to_login_when_unauthenticated()
    {
        config()->set('app.admin_password', password_hash('secret', PASSWORD_BCRYPT));

        $protectedRoutes = [
            route('virtual-hosts.index'),
            route('virtual-hosts.create'),
            route('settings.index'),
            route('virtual-hosts.sync'),
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect(route('admin.login'));
        }
    }

    public function test_password_empty_disables_auth()
    {
        config()->set('app.admin_password', '');

        $response = $this->get(route('virtual-hosts.index'));

        $response->assertStatus(200);
    }

    public function test_logout_prevents_access_to_protected_pages()
    {
        config()->set('app.admin_password', password_hash('secret', PASSWORD_BCRYPT));

        $this->post(route('admin.login.post'), ['password' => 'secret']);
        $this->post(route('admin.logout'));

        $response = $this->get(route('virtual-hosts.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_session_authentication_persists_across_requests()
    {
        config()->set('app.admin_password', password_hash('secret', PASSWORD_BCRYPT));

        $this->post(route('admin.login.post'), ['password' => 'secret']);

        $this->get(route('virtual-hosts.index'))->assertStatus(200);
        $this->get(route('virtual-hosts.create'))->assertStatus(200);
        $this->get(route('settings.index'))->assertStatus(200);
    }
}
