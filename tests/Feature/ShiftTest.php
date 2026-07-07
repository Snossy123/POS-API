<?php

namespace Tests\Feature;

use App\Models\Shift;
use Tests\TestCase;

class ShiftTest extends TestCase
{
    public function test_employee_can_open_shift(): void
    {
        $cashier = $this->actingAsCashier();

        $response = $this->postJson('/api/shifts/open', [
            'opening_float' => 500,
            'notes' => 'Morning shift',
        ]);

        $response->assertOk()
            ->assertJsonPath('shift.status', 'open')
            ->assertJsonPath('shift.opening_float', '500.00');

        $this->assertDatabaseHas('shifts', [
            'employee_id' => $cashier->id,
            'status' => 'open',
        ]);
    }

    public function test_opening_shift_twice_returns_existing_shift(): void
    {
        $cashier = $this->actingAsCashier();
        $existing = $this->openShiftFor($cashier, 100);

        $response = $this->postJson('/api/shifts/open', ['opening_float' => 200]);

        $response->assertOk()->assertJsonPath('shift.id', $existing->id);
        $this->assertSame(1, Shift::where('employee_id', $cashier->id)->count());
    }

    public function test_current_shift_endpoint_returns_open_shift(): void
    {
        $cashier = $this->actingAsCashier();
        $shift = $this->openShiftFor($cashier, 250);

        $this->getJson('/api/shifts/current')
            ->assertOk()
            ->assertJsonPath('shift.id', $shift->id);
    }

    public function test_close_shift_calculates_cash_difference(): void
    {
        $cashier = $this->actingAsCashier();
        $shift = $this->openShiftFor($cashier, 100);
        $product = $this->createProduct(['price' => 50]);
        $employee = $this->createEmployeeForSale();

        $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 1))
            ->assertOk();

        $response = $this->postJson("/api/shifts/{$shift->id}/close", [
            'actual_cash' => 160,
        ]);

        $response->assertOk()
            ->assertJsonPath('shift.status', 'closed')
            ->assertJsonStructure(['report' => ['summary', 'invoices']]);

        $this->assertSame(10.0, (float) $response->json('shift.cash_difference'));
    }

    public function test_shift_report_includes_summary_and_invoices(): void
    {
        $this->actingAsManager();
        $cashier = $this->actingAsCashier();
        $shift = $this->openShiftFor($cashier, 0);
        $product = $this->createProduct(['price' => 30]);
        $employee = $this->createEmployeeForSale();

        $this->postJson('/api/sales-invoices', $this->salePayload($product, $employee, 2))
            ->assertOk();

        $this->actingAsManager();

        $this->getJson("/api/shifts/{$shift->id}/report")
            ->assertOk()
            ->assertJsonPath('report.summary.invoice_count', 1)
            ->assertJsonPath('report.summary.net_sales', 60)
            ->assertJsonCount(1, 'report.invoices');
    }

    public function test_cashier_can_view_own_shift_report(): void
    {
        $cashier = $this->actingAsCashier();
        $shift = $this->openShiftFor($cashier);

        $this->getJson("/api/shifts/{$shift->id}/report")->assertOk();
    }

    public function test_cashier_cannot_view_other_employee_shift_report(): void
    {
        $other = $this->actingAsCashier();
        $otherShift = $this->openShiftFor($other);

        $cashier = $this->actingAsCashier();
        $this->openShiftFor($cashier);

        $this->getJson("/api/shifts/{$otherShift->id}/report")->assertForbidden();
    }
}
