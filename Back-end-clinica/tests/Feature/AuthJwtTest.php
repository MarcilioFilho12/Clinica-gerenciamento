<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Profiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthJwtTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_without_password_in_payload(): void
    {
        $user = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'profile_id' => Profiles::ADMIN,
        ]);

        $response = $this->postJson('/api/auth', [
            'email' => 'admin@example.com',
            'senha' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'profile_id']])
            ->assertJsonMissingPath('user.password');

        $this->assertSame(Profiles::ADMIN, $response->json('user.profile_id'));
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_protected_route_requires_jwt(): void
    {
        $this->getJson('/api/listar-pacientes')->assertUnauthorized();
    }

    public function test_admin_can_list_users_and_profissional_cannot(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin2@example.com',
            'password' => 'password',
            'profile_id' => Profiles::ADMIN,
        ]);

        $profissional = User::query()->create([
            'name' => 'Medico',
            'email' => 'medico@example.com',
            'password' => 'password',
            'profile_id' => Profiles::PROFISSIONAL,
        ]);

        $adminToken = $this->postJson('/api/auth', [
            'email' => 'admin2@example.com',
            'senha' => 'password',
        ])->json('token');

        $medicoToken = $this->postJson('/api/auth', [
            'email' => 'medico@example.com',
            'senha' => 'password',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$adminToken)
            ->getJson('/api/usuarios')
            ->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$medicoToken)
            ->getJson('/api/usuarios')
            ->assertForbidden();
    }
}
