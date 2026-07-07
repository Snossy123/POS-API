<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryCycleTest extends TestCase
{
    public function test_sale_deducts_stock_even_when_insufficient(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['stock' => 0, 'price' => 15]);
        $employee = $this->createEmployeeForSale();

        $response = $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 2));

        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertSame(-2, $product->fresh()->stock);
    }

    public function test_void_restores_stock(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['stock' => 10, 'price' => 20]);
        $employee = $this->createEmployeeForSale();

        $saleResponse = $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 3));
        $invoiceId = $saleResponse->json('invoice.id');

        $this->assertSame(7, $product->fresh()->stock);

        $this->patchJson("/api/sales-invoices/{$invoiceId}/void", [
            'reason' => 'test void',
        ])->assertStatus(200);

        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_full_refund_restores_stock(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['stock' => 10, 'price' => 25]);
        $employee = $this->createEmployeeForSale();
        $payload = $this->salePayload($product, $employee, 4);

        $saleResponse = $this->postJson('/api/sales-invoices', $payload);
        $invoiceId = $saleResponse->json('invoice.id');
        $total = (float) $payload['total'];

        $this->assertSame(6, $product->fresh()->stock);

        $this->postJson("/api/sales-invoices/{$invoiceId}/refund", [
            'amount' => $total,
            'reason' => 'full refund test',
        ])->assertStatus(200);

        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_update_items_adjusts_stock(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['stock' => 20, 'price' => 10]);
        $employee = $this->createEmployeeForSale();
        $payload = $this->salePayload($product, $employee, 2, [
            'payment_status' => 'unpaid',
            'amount_paid' => 0,
        ]);

        $saleResponse = $this->postJson('/api/sales-invoices', $payload);
        $invoiceId = $saleResponse->json('invoice.id');

        $this->assertSame(18, $product->fresh()->stock);

        $this->patchJson("/api/sales-invoices/{$invoiceId}/items", [
            'total' => 50,
            'items' => [
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 10,
                    'quantity' => 5,
                    'barcode' => $product->barcode,
                ],
            ],
        ])->assertStatus(200);

        $this->assertSame(15, $product->fresh()->stock);
    }

    public function test_purchase_general_increments_stock(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['stock' => 5, 'barcode' => 'PURCHASE-BARCODE-001']);

        $response = $this->postJson('/api/purchase-invoices', [
            'supplier' => 'Test Supplier',
            'date' => now()->toDateString(),
            'time' => now()->format('H:i:s'),
            'total' => 100,
            'invoice_type' => 'general',
            'items' => [
                [
                    'product_name' => $product->name,
                    'barcode' => $product->barcode,
                    'quantity' => 50,
                    'purchase_price' => 2,
                    'sale_price' => 10,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertSame(55, $product->fresh()->stock);
    }

    public function test_purchase_operation_does_not_affect_stock(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['stock' => 8]);

        $this->postJson('/api/purchase-invoices', [
            'supplier' => 'Ops Supplier',
            'date' => now()->toDateString(),
            'time' => now()->format('H:i:s'),
            'total' => 50,
            'invoice_type' => 'operation',
            'items' => [
                [
                    'product_name' => 'Electricity Bill',
                    'purchase_price' => 50,
                ],
            ],
        ])->assertStatus(200);

        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_patch_stock_delta(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['stock' => 12]);

        $this->patchJson("/api/products/{$product->id}/stock", [
            'delta' => 5,
        ])->assertStatus(200)->assertJsonPath('success', true);

        $this->assertSame(17, $product->fresh()->stock);

        $this->patchJson("/api/products/{$product->id}/stock", [
            'delta' => -7,
        ])->assertStatus(200);

        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_cashier_sale_requires_open_shift(): void
    {
        $cashier = $this->actingAsCashier();
        $this->openShiftFor($cashier);
        $product = $this->createProduct(['stock' => 5, 'price' => 10]);
        $employee = $this->createEmployeeForSale();

        $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 1))
            ->assertStatus(200);

        $this->assertSame(4, $product->fresh()->stock);
    }
}
