<?php

namespace Tests\Feature;

use App\Models\SalesInvoice;
use Tests\TestCase;

class SalesInvoiceTest extends TestCase
{
    public function test_admin_can_create_sale_without_shift(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['stock' => 10, 'price' => 20]);
        $employee = $this->createEmployeeForSale();

        $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 2))
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_cashier_requires_open_shift_to_sell(): void
    {
        $cashier = $this->actingAsCashier();
        $product = $this->createProduct();
        $employee = $this->createEmployeeForSale();

        $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee))
            ->assertStatus(422)
            ->assertJsonPath('code', 'SHIFT_REQUIRED');

        $this->openShiftFor($cashier);

        $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee))
            ->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_store_is_idempotent_with_client_id(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['price' => 10]);
        $employee = $this->createEmployeeForSale();
        $payload = $this->salePayload($product, $employee);
        $clientId = 'offline-client-123';

        $first = $this->withHeader('X-Client-Id', $clientId)
            ->postJson('/api/sales-invoices', $payload)
            ->assertOk();

        $second = $this->withHeader('X-Client-Id', $clientId)
            ->postJson('/api/sales-invoices', $payload)
            ->assertOk()
            ->assertJsonPath('message', 'Invoice already synced');

        $this->assertSame($first->json('invoice.id'), $second->json('invoice.id'));
        $this->assertSame(1, SalesInvoice::count());
    }

    public function test_manager_can_void_invoice(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['stock' => 10]);
        $employee = $this->createEmployeeForSale();

        $sale = $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 2));
        $invoiceId = $sale->json('invoice.id');

        $this->actingAsManager();

        $this->patchJson("/api/sales-invoices/{$invoiceId}/void", ['reason' => 'mistake'])
            ->assertOk()
            ->assertJsonPath('invoice.status', 'void');

        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_cashier_cannot_void_invoice(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct();
        $employee = $this->createEmployeeForSale();
        $invoiceId = $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee))
            ->json('invoice.id');

        $this->actingAsCashier();

        $this->patchJson("/api/sales-invoices/{$invoiceId}/void")
            ->assertForbidden();
    }

    public function test_partial_refund_sets_partial_refund_status(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['stock' => 10, 'price' => 100]);
        $employee = $this->createEmployeeForSale();
        $invoiceId = $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 1))
            ->json('invoice.id');

        $this->actingAsManager();

        $this->postJson("/api/sales-invoices/{$invoiceId}/refund", [
            'amount' => 40,
            'reason' => 'partial',
        ])->assertOk()->assertJsonPath('invoice.status', 'partial_refund');
    }

    public function test_pay_marks_unpaid_invoice_as_paid(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['price' => 50]);
        $employee = $this->createEmployeeForSale();

        $invoiceId = $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 1, [
            'payment_status' => 'unpaid',
            'amount_paid' => 0,
        ]))->json('invoice.id');

        $this->postJson("/api/sales-invoices/{$invoiceId}/pay", [
            'payment_method' => 'cash',
            'amount_paid' => 50,
        ])->assertOk()->assertJsonPath('invoice.payment_status', 'paid');
    }

    public function test_manager_can_update_payment_status(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct();
        $employee = $this->createEmployeeForSale();

        $invoiceId = $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 1, [
            'payment_status' => 'unpaid',
            'amount_paid' => 0,
        ]))->json('invoice.id');

        $this->actingAsManager();

        $this->patchJson("/api/sales-invoices/{$invoiceId}/payment-status", [
            'payment_status' => 'partial',
        ])->assertOk()->assertJsonPath('invoice.payment_status', 'partial');
    }

    public function test_reprint_returns_invoice(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct();
        $employee = $this->createEmployeeForSale();

        $invoiceId = $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee))
            ->json('invoice.id');

        $this->postJson("/api/sales-invoices/{$invoiceId}/reprint")
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['invoice']);
    }
}
