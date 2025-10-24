<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_and_receive_token(): void
    {
        $res = $this->postJson('/api/register', [
            'name' => 'Amr',
            'email' => 'amr@test.com',
            'password' => 'secret123',
        ])->assertOk();

        $this->assertIsString($res->json('token'));
    }

    public function test_can_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret123')]);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertOk();

        $token = $login->json('token');

        $this->withHeader('Authorization', "Bearer $token")
             ->postJson('/api/logout')
             ->assertNoContent();
    }
}