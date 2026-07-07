<?php

namespace Tests;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Product;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['license.enforce' => false]);
    }

    protected function authenticateSanctum(Employee|User $user): Employee|User
    {
        $this->defaultHeaders = ['Accept' => 'application/json'];
        Sanctum::actingAs($user);

        return $user;
    }

    protected function actingAsAdmin(): User
    {
        return $this->authenticateSanctum(
            User::factory()->create(['role' => 'admin'])
        );
    }

    protected function actingAsManager(): Employee
    {
        return $this->authenticateSanctum(Employee::create([
            'name' => 'Manager Test',
            'email' => 'manager-test-'.uniqid().'@pos.local',
            'password' => 'password',
            'role' => 'manager',
            'active' => true,
            'salary' => 0,
            'hiring_date' => now()->toDateString(),
        ]));
    }

    protected function actingAsCashier(): Employee
    {
        return $this->authenticateSanctum(Employee::create([
            'name' => 'Cashier Test',
            'email' => 'cashier-test-'.uniqid().'@pos.local',
            'password' => 'password',
            'role' => 'cashier',
            'active' => true,
            'salary' => 0,
            'hiring_date' => now()->toDateString(),
        ]));
    }

    protected function openShiftFor(Employee $employee, float $openingFloat = 0): Shift
    {
        return Shift::create([
            'employee_id' => $employee->id,
            'opened_at' => now(),
            'opening_float' => $openingFloat,
            'status' => 'open',
        ]);
    }

    protected function createCategory(array $attributes = []): Category
    {
        return Category::create(array_merge([
            'name' => 'Category '.uniqid(),
            'description' => 'Test category',
            'color' => '#000000',
        ], $attributes));
    }

    protected function createProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Test Product '.uniqid(),
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

    protected function createSaleInvoice(
        Product $product,
        Employee $employee,
        ?Shift $shift = null,
        array $overrides = []
    ): SalesInvoice {
        $quantity = (float) ($overrides['quantity'] ?? 1);
        unset($overrides['quantity']);

        $total = (float) $product->price * $quantity;
        $date = $overrides['date'] ?? now()->toDateString();

        $invoice = SalesInvoice::create(array_merge([
            'invoice_number' => $overrides['invoice_number'] ?? 'INV-'.uniqid(),
            'date' => $date,
            'time' => now()->format('H:i:s'),
            'employee_id' => $employee->id,
            'shift_id' => $shift?->id,
            'total' => $total,
            'payment_method' => 'cash',
            'amount_paid' => $total,
            'change_given' => 0,
            'kitchen_note' => '',
            'order_type' => 'takeaway',
            'status' => 'completed',
            'payment_status' => 'paid',
        ], $overrides));

        SalesInvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => (float) $product->price,
            'quantity' => $quantity,
            'barcode' => $product->barcode ?? '',
        ]);

        return $invoice->fresh(['items', 'employee']);
    }

    protected function purchasePayload(Product $product, array $overrides = []): array
    {
        return array_merge([
            'supplier' => 'Test Supplier',
            'date' => now()->toDateString(),
            'time' => now()->format('H:i:s'),
            'total' => 100,
            'invoice_type' => 'general',
            'items' => [
                [
                    'product_name' => $product->name,
                    'barcode' => $product->barcode,
                    'quantity' => 10,
                    'purchase_price' => 2,
                    'sale_price' => (float) $product->price,
                ],
            ],
        ], $overrides);
    }
}
