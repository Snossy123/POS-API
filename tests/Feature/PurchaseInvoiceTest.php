<?php

namespace Tests\Feature;

use App\Models\PurchaseInvoice;
use Tests\TestCase;

class PurchaseInvoiceTest extends TestCase
{
    public function test_next_number_has_expected_prefix(): void
    {
        $this->actingAsManager();

        $response = $this->getJson('/api/purchase-invoices/next-number')->assertOk();

        $this->assertStringStartsWith('PUR-'.now()->format('Ymd').'-', $response->json('invoice_number'));
    }

    public function test_manager_can_create_general_purchase_invoice(): void
    {
        $this->actingAsManager();
        $product = $this->createProduct(['stock' => 5]);

        $this->postJson('/api/purchase-invoices', $this->purchasePayload($product))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('purchase_invoices', ['supplier' => 'Test Supplier']);
        $this->assertSame(15, $product->fresh()->stock);
    }

    public function test_index_can_filter_by_invoice_type(): void
    {
        $this->actingAsAdmin();

        PurchaseInvoice::create([
            'invoice_number' => 'PUR-GEN-001',
            'supplier' => 'General Supplier',
            'invoice_type' => 'general',
            'date' => now()->toDateString(),
            'time' => '10:00:00',
            'total' => 100,
        ]);

        PurchaseInvoice::create([
            'invoice_number' => 'PUR-OPS-001',
            'supplier' => 'Ops Supplier',
            'invoice_type' => 'operation',
            'date' => now()->toDateString(),
            'time' => '11:00:00',
            'total' => 50,
        ]);

        $this->getJson('/api/purchase-invoices?invoice_type=operation')
            ->assertOk()
            ->assertJsonCount(1, 'invoices')
            ->assertJsonPath('invoices.0.invoice_type', 'operation');
    }

    public function test_cashier_cannot_create_purchase_invoice(): void
    {
        $this->actingAsCashier();
        $product = $this->createProduct();

        $this->postJson('/api/purchase-invoices', $this->purchasePayload($product))
            ->assertForbidden();
    }
}
