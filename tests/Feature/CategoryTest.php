<?php

namespace Tests\Feature;

use Tests\TestCase;

class CategoryTest extends TestCase
{
    public function test_authenticated_user_can_list_categories(): void
    {
        $this->actingAsCashier();
        $this->createCategory();

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonCount(1, 'categories');
    }

    public function test_manager_can_create_update_and_delete_category(): void
    {
        $this->actingAsManager();

        $create = $this->postJson('/api/categories', [
            'action' => 'add',
            'category' => [
                'name' => 'Hot Drinks',
                'description' => 'Coffee and tea',
                'color' => '#ff0000',
            ],
        ]);

        $create->assertOk()->assertJsonPath('success', true);
        $categoryId = \App\Models\Category::where('name', 'Hot Drinks')->value('id');

        $this->postJson('/api/categories', [
            'action' => 'update',
            'category' => [
                'id' => $categoryId,
                'name' => 'Cold Drinks',
                'description' => 'Updated',
                'color' => '#00ff00',
            ],
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('categories', ['id' => $categoryId, 'name' => 'Cold Drinks']);

        $this->postJson('/api/categories', [
            'action' => 'delete',
            'id' => $categoryId,
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
    }

    public function test_cashier_cannot_mutate_categories(): void
    {
        $this->actingAsCashier();
        $category = $this->createCategory();

        $this->postJson('/api/categories', [
            'action' => 'delete',
            'id' => $category->id,
        ])->assertForbidden();
    }
}
