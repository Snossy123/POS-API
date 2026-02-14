<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiGetTest extends TestCase
{
    /**
     * Test GET /api/products
     */
    public function test_get_products_returns_successful_response(): void
    {
        $response = $this->get('/api/products');
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'products']);
    }

    /**
     * Test GET /api/categories
     */
    public function test_get_categories_returns_successful_response(): void
    {
        $response = $this->get('/api/categories');
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'categories']);
    }

    /**
     * Test GET /api/employees
     */
    public function test_get_employees_returns_successful_response(): void
    {
        $response = $this->get('/api/employees');
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'employees']);
    }

    /**
     * Test GET /api/purchase-invoices
     */
    public function test_get_purchase_invoices_returns_successful_response(): void
    {
        $response = $this->get('/api/purchase-invoices');
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'invoices']);
    }

    /**
     * Test GET /api/sales-invoices
     */
    public function test_get_sales_invoices_returns_successful_response(): void
    {
        $response = $this->get('/api/sales-invoices');
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'invoices']);
    }

    /**
     * Test GET /api/reports
     */
    public function test_get_reports_returns_successful_response(): void
    {
        $response = $this->get('/api/reports?type=sales');
        $response->assertStatus(200);
        // Returns array directly
        $response->assertJsonIsArray();
    }
}
