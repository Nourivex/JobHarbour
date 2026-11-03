<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Keamanan sesi: masa berlaku token admin yang lebih pendek, keluar dari semua perangkat,
 * dan ganti kata sandi yang mengeluarkan perangkat lain.
 */
class SessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['admin', 'recruiter'] as $role) {
            User::create(['name' => ucfirst($role), 'email' => "{$role}@test.com", 'password' => Hash::make('secret123'), 'role' => $role]);
        }
    }

    private function login(string $email, string $password = 'secret123'): array
    {
        $this->flushHeaders();

        return $this->postJson('/api/auth/login', ['email' => $email, 'password' => $password])->assertStatus(200)->json('data');
    }

    private function me(string $token)
    {
        return $this->flushHeaders()->withHeaders(['Authorization' => "Bearer {$token}"])->getJson('/api/auth/me');
    }

    public function test_admin_token_expires_sooner_than_other_roles(): void
    {
        $admin = $this->login('admin@test.com');
        $recruiter = $this->login('recruiter@test.com');

        $this->assertSame(JwtService::ADMIN_TTL, $admin['expires_in']);
        $this->assertSame(86400 * 7, $recruiter['expires_in']);

        $exp = app(JwtService::class)->decodeToken($admin['token'])->exp;
        $this->assertEqualsWithDelta(time() + JwtService::ADMIN_TTL, $exp, 5);
    }

    public function test_logout_all_revokes_every_token(): void
    {
        $laptop = $this->login('recruiter@test.com')['token'];
        $phone = $this->login('recruiter@test.com')['token'];

        $this->me($phone)->assertStatus(200);
        $this->flushHeaders()->withHeaders(['Authorization' => "Bearer {$laptop}"])->postJson('/api/auth/logout-all')->assertStatus(200);

        $this->me($laptop)->assertStatus(401)->assertJsonPath('error_code', 'TOKEN_REVOKED');
        $this->me($phone)->assertStatus(401)->assertJsonPath('error_code', 'TOKEN_REVOKED');

        $fresh = $this->login('recruiter@test.com')['token'];
        $this->me($fresh)->assertStatus(200);
    }

    public function test_password_change_keeps_this_device_and_signs_out_others(): void
    {
        $other = $this->login('admin@test.com')['token'];
        $current = $this->login('admin@test.com')['token'];

        $newToken = $this->flushHeaders()->withHeaders(['Authorization' => "Bearer {$current}"])->putJson('/api/auth/password', [
            'current_password' => 'secret123', 'password' => 'secret456', 'password_confirmation' => 'secret456',
        ])->assertStatus(200)->json('data.token');

        $this->me($other)->assertStatus(401)->assertJsonPath('error_code', 'TOKEN_REVOKED');
        $this->me($current)->assertStatus(401);
        $this->me($newToken)->assertStatus(200);
    }
}
