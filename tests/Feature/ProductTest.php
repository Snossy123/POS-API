<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_authenticated_user_can_list_products(): void
    {
        $this->actingAsCashier();
        $this->createProduct();

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'products');
    }

    public function test_manager_can_add_product(): void
    {
        $this->actingAsManager();

        $response = $this->postJson('/api/products', [
            'action' => 'add',
            'product' => [
                'name' => 'Latte',
                'price' => 25,
                'stock' => 10,
                'hasSizes' => 0,
            ],
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('products', ['name' => 'Latte']);
    }

    public function test_duplicate_product_name_is_rejected(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['name' => 'Duplicate Name']);

        $this->postJson('/api/products', [
            'action' => 'add',
            'product' => [
                'name' => $product->name,
                'price' => 10,
                'stock' => 1,
                'hasSizes' => 0,
            ],
        ])->assertOk()->assertJsonPath('success', false);
    }

    public function test_manager_can_update_and_delete_product(): void
    {
        $this->actingAsManager();
        $product = $this->createProduct(['name' => 'Old Name']);

        $this->postJson('/api/products', [
            'action' => 'update',
            'product' => [
                'id' => $product->id,
                'name' => 'New Name',
                'price' => 15,
                'stock' => 5,
                'hasSizes' => 0,
            ],
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'New Name']);

        $this->postJson('/api/products', [
            'action' => 'delete',
            'id' => $product->id,
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_stock_can_be_set_directly(): void
    {
        $this->actingAsAdmin();
        $product = $this->createProduct(['stock' => 4]);

        $this->patchJson("/api/products/{$product->id}/stock", [
            'stock' => 99,
        ])->assertOk();

        $this->assertSame(99, $product->fresh()->stock);
    }
}
