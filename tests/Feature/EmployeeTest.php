<?php

namespace Tests\Feature;

use App\Models\Employee;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    public function test_admin_can_add_update_and_delete_employee(): void
    {
        $this->actingAsAdmin();

        $create = $this->postJson('/api/employees', [
            'action' => 'add',
            'employee' => [
                'name' => 'New Staff',
                'email' => 'staff@pos.test',
                'password' => 'password123',
                'role' => 'cashier',
                'phone' => '01000000000',
                'salary' => 3000,
                'hiring_date' => now()->toDateString(),
                'active' => true,
            ],
        ]);

        $create->assertOk()->assertJsonPath('success', true);
        $employeeId = Employee::where('email', 'staff@pos.test')->value('id');

        $this->postJson('/api/employees', [
            'action' => 'update',
            'employee' => [
                'id' => $employeeId,
                'name' => 'Updated Staff',
                'email' => 'staff@pos.test',
                'role' => 'manager',
                'phone' => '01000000001',
                'salary' => 4000,
                'hiring_date' => now()->toDateString(),
                'active' => true,
            ],
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('employees', ['id' => $employeeId, 'name' => 'Updated Staff']);

        $this->postJson('/api/employees', [
            'action' => 'delete',
            'id' => $employeeId,
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseMissing('employees', ['id' => $employeeId]);
    }

    public function test_duplicate_employee_email_is_rejected(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/employees', [
            'action' => 'add',
            'employee' => [
                'name' => 'First',
                'email' => 'duplicate@pos.test',
                'password' => 'password',
                'role' => 'cashier',
                'phone' => '0100',
                'salary' => 1000,
                'hiring_date' => now()->toDateString(),
                'active' => true,
            ],
        ])->assertOk()->assertJsonPath('success', true);

        $this->postJson('/api/employees', [
            'action' => 'add',
            'employee' => [
                'name' => 'Second',
                'email' => 'duplicate@pos.test',
                'password' => 'password',
                'role' => 'cashier',
                'phone' => '0101',
                'salary' => 1000,
                'hiring_date' => now()->toDateString(),
                'active' => true,
            ],
        ])->assertOk()->assertJsonPath('success', false);
    }

    public function test_manager_cannot_manage_employees(): void
    {
        $this->actingAsManager();

        $this->postJson('/api/employees', [
            'action' => 'add',
            'employee' => [
                'name' => 'Blocked',
                'email' => 'mgr-blocked@pos.test',
                'password' => 'password',
                'role' => 'cashier',
                'phone' => '0100',
                'salary' => 1000,
                'hiring_date' => now()->toDateString(),
                'active' => true,
            ],
        ])->assertForbidden();
    }
}
