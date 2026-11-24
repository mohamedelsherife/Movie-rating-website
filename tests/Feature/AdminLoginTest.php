<?php

namespace Tests\Feature;

use App\Models\Admin;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): Admin
    {
        $this->seed(AdminSeeder::class);

        return Admin::firstWhere('email', 'admin');
    }

    public function test_seeder_creates_one_admin_with_a_hashed_password(): void
    {
        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class); // running it twice must not duplicate

        $this->assertSame(1, Admin::count());
        $admin = Admin::first();
        $this->assertSame('admin', $admin->email);
        $this->assertNotSame('admin', $admin->password);
        $this->assertTrue(Hash::check('admin', $admin->password));
    }

    public function test_login_page_is_shown(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('name="email"', false)
            ->assertSee('type="password"', false)
            ->assertSee('name="remember"', false)
            ->assertSee('data-password-toggle', false)
            ->assertDontSee('class="navbar"', false);
    }

    public function test_admin_pages_require_login(): void
    {
        foreach ([
            route('admin.dashboard'),
            route('admin.movies.index'),
            route('admin.movies.create'),
            route('admin.series.index'),
            route('admin.series.create'),
            route('admin.movies.edit', 1),
            route('admin.series.edit', 1),
        ] as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }

        $this->post(route('admin.movies.store'))->assertRedirect(route('admin.login'));
        $this->put(route('admin.movies.update', 1))->assertRedirect(route('admin.login'));
        $this->put(route('admin.series.update', 1))->assertRedirect(route('admin.login'));
        $this->delete(route('admin.series.destroy', 1))->assertRedirect(route('admin.login'));
        $this->get('/')->assertRedirect('/admin');
    }

    public function test_admin_can_log_in_with_default_credentials(): void
    {
        $admin = $this->seedAdmin();

        $this->post(route('admin.login.submit'), ['email' => 'admin', 'password' => 'admin'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Log out');
    }

    public function test_login_returns_to_the_page_that_was_requested(): void
    {
        $this->seedAdmin();

        $this->get(route('admin.series.index'))->assertRedirect(route('admin.login'));

        $this->post(route('admin.login.submit'), ['email' => 'admin', 'password' => 'admin'])
            ->assertRedirect(route('admin.series.index'));
    }

    public function test_wrong_password_is_rejected_with_a_clear_message(): void
    {
        $this->seedAdmin();

        $this->from(route('admin.login'))
            ->post(route('admin.login.submit'), ['email' => 'admin', 'password' => 'wrong'])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('login')
            ->assertSessionHasInput('email', 'admin')
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest('admin');

        $this->from(route('admin.login'))->followingRedirects()
            ->post(route('admin.login.submit'), ['email' => 'admin', 'password' => 'wrong'])
            ->assertSee('Invalid email or password')
            ->assertSee('value="admin"', false);
    }

    public function test_login_fields_are_required(): void
    {
        $this->post(route('admin.login.submit'), [])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest('admin');
    }

    public function test_remember_me_sets_the_remember_cookie(): void
    {
        $admin = $this->seedAdmin();

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin', 'password' => 'admin', 'remember' => '1',
        ]);

        $response->assertCookie(\Illuminate\Support\Facades\Auth::guard('admin')->getRecallerName());
        $this->assertNotNull($admin->fresh()->remember_token);
    }

    public function test_logged_in_admin_is_redirected_away_from_login_page(): void
    {
        $this->actingAs($this->seedAdmin(), 'admin');

        $this->get(route('admin.login'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_log_out(): void
    {
        $this->actingAs($this->seedAdmin(), 'admin');

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.movies.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.series.index'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_stays_logged_in_across_pages_and_refreshes(): void
    {
        $this->seedAdmin();

        $this->post(route('admin.login.submit'), ['email' => 'admin', 'password' => 'admin']);

        foreach ([
            route('admin.movies.index'),
            route('admin.movies.index'), // refresh
            route('admin.movies.create'),
            route('admin.series.index'),
            route('admin.series.create'),
            route('admin.dashboard'),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->assertAuthenticated('admin');
    }

    public function test_regular_users_cannot_reach_the_admin_panel(): void
    {
        \App\Models\User::factory()->create(['email' => 'test@example.com']);

        $this->post(route('admin.login.submit'), ['email' => 'test@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest('admin');
    }
}
