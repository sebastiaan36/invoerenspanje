<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_filament_login_page_no_longer_exists()
    {
        // Admins moeten via Fortify /login (2FA afgedwongen), niet via /admin/login.
        $this->get('/admin/login')->assertNotFound();
    }

    public function test_guests_are_redirected_to_the_fortify_login()
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_klant_cannot_access_the_admin_panel()
    {
        $klant = User::factory()->create(['role' => 'klant']);

        $this->actingAs($klant)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_access_the_admin_panel()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')->assertOk();
    }
}
