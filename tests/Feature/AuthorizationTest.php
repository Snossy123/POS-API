<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    public function test_cashier_cannot_manage_products(): void
    {
        $this->actingAsCashier();
        $product = $this->createProduct();

        $this->postJson('/api/products', [
            'action' => 'add',
            'product' => [
                'name' => 'New Product',
                'price' => 5,
                'stock' => 1,
                'hasSizes' => 0,
            ],
        ])->assertForbidden();

        $this->postJson('/api/products', [
            'action' => 'update',
            'product' => [
                'id' => $product->id,
                'name' => 'Updated',
                'price' => 5,
                'stock' => 1,
                'hasSizes' => 0,
            ],
        ])->assertForbidden();

        $this->patchJson("/api/products/{$product->id}/stock", ['delta' => 1])
            ->assertForbidden();
    }

    public function test_manager_can_manage_products(): void
    {
        $this->actingAsManager();

        $this->postJson('/api/products', [
            'action' => 'add',
            'product' => [
                'name' => 'Manager Product',
                'price' => 12,
                'stock' => 3,
                'hasSizes' => 0,
            ],
        ])->assertOk()->assertJsonPath('success', true);
    }

    public function test_cashier_cannot_manage_categories(): void
    {
        $this->actingAsCashier();

        $this->postJson('/api/categories', [
            'action' => 'add',
            'category' => ['name' => 'Blocked Category', 'color' => '#fff'],
        ])->assertForbidden();
    }

    public function test_cashier_cannot_access_purchase_invoices(): void
    {
        $this->actingAsCashier();
        $product = $this->createProduct();

        $this->getJson('/api/purchase-invoices')->assertForbidden();
        $this->getJson('/api/purchase-invoices/next-number')->assertForbidden();
        $this->postJson('/api/purchase-invoices', $this->purchasePayload($product))->assertForbidden();
    }

    public function test_manager_can_access_purchase_invoices(): void
    {
        $this->actingAsManager();

        $this->getJson('/api/purchase-invoices')->assertOk();
        $this->getJson('/api/purchase-invoices/next-number')
            ->assertOk()
            ->assertJsonStructure(['invoice_number']);
    }

    public function test_cashier_cannot_view_reports(): void
    {
        $this->actingAsCashier();

        $this->getJson('/api/reports?type=sales')->assertForbidden();
    }

    public function test_manager_can_view_reports(): void
    {
        $this->actingAsManager();

        $this->getJson('/api/reports?type=sales')->assertOk();
    }

    public function test_cashier_cannot_list_shifts(): void
    {
        $this->actingAsCashier();

        $this->getJson('/api/shifts')->assertForbidden();
    }

    public function test_manager_can_list_shifts(): void
    {
        $this->actingAsManager();

        $this->getJson('/api/shifts')->assertOk();
    }

    public function test_cashier_employee_index_is_redacted(): void
    {
        $this->actingAsCashier();
        $this->createEmployeeForSale();

        $response = $this->getJson('/api/employees')->assertOk();
        $employee = $response->json('employees.0');

        $this->assertArrayHasKey('id', $employee);
        $this->assertArrayHasKey('name', $employee);
        $this->assertArrayNotHasKey('salary', $employee);
        $this->assertArrayNotHasKey('email', $employee);
    }

    public function test_admin_employee_index_includes_sensitive_fields(): void
    {
        $this->actingAsAdmin();
        $this->createEmployeeForSale();

        $response = $this->getJson('/api/employees')->assertOk();
        $employee = $response->json('employees.0');

        $this->assertArrayHasKey('email', $employee);
        $this->assertArrayHasKey('salary', $employee);
    }

    public function test_cashier_cannot_manage_employees(): void
    {
        $this->actingAsCashier();

        $this->postJson('/api/employees', [
            'action' => 'add',
            'employee' => [
                'name' => 'Blocked',
                'email' => 'blocked@pos.test',
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
