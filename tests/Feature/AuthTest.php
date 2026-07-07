<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_admin_login_success_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@pos.test',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/auth/admin/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role', 'type']]);
    }

    public function test_admin_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'admin@pos.test',
            'password' => Hash::make('secret123'),
        ]);

        $this->postJson('/api/auth/admin/login', [
            'email' => 'admin@pos.test',
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }

    public function test_employee_login_success_returns_token(): void
    {
        $employee = Employee::create([
            'name' => 'Login Employee',
            'email' => 'employee@pos.test',
            'password' => Hash::make('secret123'),
            'role' => 'cashier',
            'active' => true,
            'salary' => 0,
            'hiring_date' => now()->toDateString(),
        ]);

        $response = $this->postJson('/api/auth/employee/login', [
            'email' => $employee->email,
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_inactive_employee_cannot_login(): void
    {
        Employee::create([
            'name' => 'Inactive Employee',
            'email' => 'inactive@pos.test',
            'password' => Hash::make('secret123'),
            'role' => 'cashier',
            'active' => false,
            'salary' => 0,
            'hiring_date' => now()->toDateString(),
        ]);

        $this->postJson('/api/auth/employee/login', [
            'email' => 'inactive@pos.test',
            'password' => 'secret123',
        ])->assertStatus(422);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $admin = $this->actingAsAdmin();

        $this->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.id', $admin->id)
            ->assertJsonPath('user.type', 'admin');
    }

    public function test_logout_revokes_token(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/products')->assertStatus(401);
    }
}
