<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiGetTest extends TestCase
{
    public function test_protected_endpoints_require_authentication(): void
    {
        $routes = [
            '/api/products',
            '/api/categories',
            '/api/employees',
            '/api/purchase-invoices',
            '/api/sales-invoices',
            '/api/reports?type=sales',
            '/api/shifts',
        ];

        foreach ($routes as $route) {
            $this->getJson($route)->assertStatus(401);
        }
    }

    public function test_authenticated_admin_can_fetch_core_resources(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/products')->assertOk()->assertJsonStructure(['status', 'products']);
        $this->getJson('/api/categories')->assertOk()->assertJsonStructure(['status', 'categories']);
        $this->getJson('/api/employees')->assertOk()->assertJsonStructure(['status', 'employees']);
        $this->getJson('/api/purchase-invoices')->assertOk()->assertJsonStructure(['status', 'invoices']);
        $this->getJson('/api/sales-invoices')->assertOk()->assertJsonStructure(['status', 'invoices']);
        $this->getJson('/api/reports?type=sales')->assertOk()->assertJsonIsArray();
        $this->getJson('/api/shifts')->assertOk()->assertJsonStructure(['status', 'shifts']);
    }
}
