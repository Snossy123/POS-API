<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReportTest extends TestCase
{
    #[DataProvider('reportTypesProvider')]
    public function test_manager_can_fetch_each_report_type(string $type): void
    {
        $this->actingAsManager();
        $from = now()->startOfMonth()->toDateString();
        $to = now()->endOfMonth()->toDateString();

        $this->getJson("/api/reports?type={$type}&from={$from}&to={$to}")
            ->assertOk()
            ->assertJsonIsArray();
    }

    public static function reportTypesProvider(): array
    {
        return [
            ['sales'],
            ['purchases'],
            ['profits'],
            ['top-selling'],
            ['purchased-items'],
            ['sold-items'],
        ];
    }

    public function test_voided_sales_are_excluded_from_sales_report(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['price' => 100]);
        $employee = $this->createEmployeeForSale();
        $date = now()->toDateString();

        $active = $this->createSaleInvoice($product, $employee, null, ['date' => $date, 'total' => 100]);
        $voided = $this->createSaleInvoice($product, $employee, null, [
            'date' => $date,
            'total' => 200,
            'status' => 'void',
        ]);

        unset($active, $voided);

        $response = $this->getJson("/api/reports?type=sales&from={$date}&to={$date}")
            ->assertOk();

        $rows = collect($response->json());
        $this->assertSame(1, $rows->sum('invoices'));
        $this->assertSame(100.0, (float) $rows->sum('total'));
    }

    public function test_invalid_report_type_returns_400(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/reports?type=unknown')
            ->assertStatus(400)
            ->assertJsonPath('error', 'Invalid report type');
    }

    public function test_cashier_cannot_access_reports(): void
    {
        $this->actingAsCashier();

        $this->getJson('/api/reports?type=sales')->assertForbidden();
    }
}
