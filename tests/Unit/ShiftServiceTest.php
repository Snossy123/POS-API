<?php

namespace Tests\Unit;

use App\Models\Shift;
use App\Services\ShiftService;
use Tests\TestCase;

class ShiftServiceTest extends TestCase
{
    private ShiftService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ShiftService::class);
    }

    public function test_calculate_expected_cash_includes_opening_float_and_cash_sales(): void
    {
        $cashier = $this->actingAsCashier();
        $shift = $this->openShiftFor($cashier, 100);
        $product = $this->createProduct(['price' => 50]);
        $employee = $this->createEmployeeForSale();

        $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 1, [
            'payment_method' => 'cash',
        ]))->assertOk();

        $shift->refresh();
        $shift->expected_cash = $this->service->calculateExpectedCash($shift);

        $this->assertSame(150.0, (float) $shift->expected_cash);
    }

    public function test_calculate_expected_cash_ignores_card_sales(): void
    {
        $cashier = $this->actingAsCashier();
        $shift = $this->openShiftFor($cashier, 100);
        $product = $this->createProduct(['price' => 80]);
        $employee = $this->createEmployeeForSale();

        $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 1, [
            'payment_method' => 'card',
        ]))->assertOk();

        $expected = $this->service->calculateExpectedCash($shift->fresh());

        $this->assertSame(100.0, $expected);
    }

    public function test_build_report_summarizes_completed_and_voided_invoices(): void
    {
        $cashier = $this->actingAsCashier();
        $shift = $this->openShiftFor($cashier, 0);
        $product = $this->createProduct(['price' => 40]);
        $employee = $this->createEmployeeForSale();

        $completedId = $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 1))
            ->json('invoice.id');

        $voidId = $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 1))
            ->json('invoice.id');

        $this->actingAsAdmin();
        $this->patchJson("/api/sales-invoices/{$voidId}/void")->assertOk();

        $report = $this->service->buildReport(Shift::find($shift->id));

        $this->assertSame(1, $report['summary']['invoice_count']);
        $this->assertSame(1, $report['summary']['void_count']);
        $this->assertSame(40.0, (float) $report['summary']['net_sales']);
        $this->assertCount(1, $report['invoices']);
        $this->assertSame($completedId, $report['invoices'][0]['id']);
    }
}
