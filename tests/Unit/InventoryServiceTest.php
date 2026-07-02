<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\SalesInvoiceItem;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;
    private InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InventoryService::class);
    }

    public function test_deduct_for_sale_reduces_stock(): void
    {
        $product = $this->createProduct(['stock' => 10]);

        $this->service->deductForSale([
            ['product_id' => $product->id, 'quantity' => 3],
        ]);

        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_deduct_for_sale_allows_negative_stock(): void
    {
        $product = $this->createProduct(['stock' => 2]);

        $this->service->deductForSale([
            ['product_id' => $product->id, 'quantity' => 5],
        ]);

        $this->assertSame(-3, $product->fresh()->stock);
    }

    public function test_restore_for_invoice_items_restores_full(): void
    {
        $product = $this->createProduct(['stock' => 3]);
        $item = new SalesInvoiceItem([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 4,
            'price' => 10,
        ]);

        $this->service->restoreForInvoiceItems([$item]);

        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_restore_partial_by_product_id(): void
    {
        $product = $this->createProduct(['stock' => 1, 'name' => 'Cola']);
        $item = new SalesInvoiceItem([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 10,
            'price' => 5,
        ]);

        $this->service->restorePartial([$item], [
            ['product_id' => $product->id, 'quantity' => 4],
        ]);

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_adjust_for_item_changes_handles_delta(): void
    {
        $product = $this->createProduct(['stock' => 10]);
        $oldItem = new SalesInvoiceItem([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'price' => 10,
        ]);

        $this->service->adjustForItemChanges([$oldItem], [
            ['product_id' => $product->id, 'quantity' => 5],
        ]);

        $this->assertSame(7, $product->fresh()->stock);

        $this->service->adjustForItemChanges([
            new SalesInvoiceItem([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => 5,
                'price' => 10,
            ]),
        ], [
            ['product_id' => $product->id, 'quantity' => 1],
        ]);

        $this->assertSame(11, $product->fresh()->stock);
    }

    public function test_add_for_purchase_increments_stock(): void
    {
        $product = $this->createProduct(['stock' => 5]);

        $this->service->addForPurchase([
            ['product_id' => $product->id, 'quantity' => 10],
        ]);

        $this->assertSame(15, $product->fresh()->stock);
    }

    public function test_adjust_stock_applies_delta(): void
    {
        $product = $this->createProduct(['stock' => 10]);

        $this->service->adjustStock($product->id, 5);
        $this->assertSame(15, $product->fresh()->stock);

        $this->service->adjustStock($product->id, -3);
        $this->assertSame(12, $product->fresh()->stock);
    }

    public function test_deduct_for_sale_aggregates_duplicate_product_lines(): void
    {
        $product = $this->createProduct(['stock' => 20]);

        $this->service->deductForSale([
            ['product_id' => $product->id, 'quantity' => 2],
            ['product_id' => $product->id, 'quantity' => 3],
        ]);

        $this->assertSame(15, $product->fresh()->stock);
    }
}
