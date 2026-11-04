<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionExpiredUxTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_logout_when_authenticated_logs_out(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($user)
            ->get('/logout')
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_get_logout_when_guest_redirects_to_login(): void
    {
        $this->get('/logout')
            ->assertRedirect('/login');
    }
}
