<?php

namespace Tests;

use App\Models\Employee;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['license.enforce' => false]);
    }

    protected function actingAsAdmin(): User
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        Sanctum::actingAs($user);

        return $user;
    }

    protected function actingAsManager(): Employee
    {
        $employee = Employee::create([
            'name' => 'Manager Test',
            'email' => 'manager-test@pos.local',
            'password' => 'password',
            'role' => 'manager',
            'active' => true,
            'salary' => 0,
            'hiring_date' => now()->toDateString(),
        ]);

        Sanctum::actingAs($employee);

        return $employee;
    }

    protected function actingAsCashier(): Employee
    {
        $employee = Employee::create([
            'name' => 'Cashier Test',
            'email' => 'cashier-test@pos.local',
            'password' => 'password',
            'role' => 'cashier',
            'active' => true,
            'salary' => 0,
            'hiring_date' => now()->toDateString(),
        ]);

        Sanctum::actingAs($employee);

        return $employee;
    }

    protected function openShiftFor(Employee $employee): Shift
    {
        return Shift::create([
            'employee_id' => $employee->id,
            'opened_at' => now(),
            'opening_float' => 0,
            'status' => 'open',
        ]);
    }

    protected function createProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Test Product',
            'hasSizes' => false,
            'price' => 10,
            's_price' => 0,
            'm_price' => 0,
            'l_price' => 0,
            'stock' => 10,
            'barcode' => 'TEST-'.uniqid(),
        ], $attributes));
    }

    protected function createEmployeeForSale(): Employee
    {
        return Employee::create([
            'name' => 'Sale Employee',
            'email' => 'sale-employee-'.uniqid().'@pos.local',
            'password' => 'password',
            'role' => 'cashier',
            'active' => true,
            'salary' => 0,
            'hiring_date' => now()->toDateString(),
        ]);
    }

    protected function salePayload(Product $product, Employee $employee, float $quantity = 1, array $overrides = []): array
    {
        $total = (float) $product->price * $quantity;

        return array_merge([
            'invoiceNumber' => 'INV-'.uniqid(),
            'date' => now()->toDateString(),
            'time' => now()->format('H:i:s'),
            'employee_id' => $employee->id,
            'total' => $total,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'order_type' => 'takeaway',
            'items' => [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'quantity' => $quantity,
                    'barcode' => $product->barcode,
                ],
            ],
        ], $overrides);
    }
}
