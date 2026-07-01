<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MenuProductSeeder extends Seeder
{
    /**
     * Seed menu products from data file. Preserves existing product images.
     */
    public function run(): void
    {
        $menu = require database_path('seeders/data/menu_products.php');

        $created = 0;
        $updatedWithImage = 0;
        $updated = 0;
        $skippedCategories = 0;

        foreach ($menu as $categoryName => $products) {
            $category = Category::where('name', $categoryName)->first();

            if (!$category) {
                $this->command?->warn("Category not found: {$categoryName}");
                $skippedCategories++;

                continue;
            }

            foreach ($products as $item) {
                $attrs = [
                    'category_id' => $category->id,
                    'hasSizes' => (int) ($item['hasSizes'] ?? false),
                    'price' => $item['price'] ?? 0,
                    's_price' => $item['s_price'] ?? 0,
                    'm_price' => $item['m_price'] ?? 0,
                    'l_price' => $item['l_price'] ?? 0,
                    'stock' => 0,
                    'barcode' => null,
                ];

                $existing = Product::where('name', $item['name'])->first();

                if ($existing && $existing->image) {
                    $existing->update($attrs);
                    $updatedWithImage++;

                    continue;
                }

                $product = Product::updateOrCreate(
                    ['name' => $item['name']],
                    array_merge($attrs, ['image' => null]),
                );

                $product->wasRecentlyCreated ? $created++ : $updated++;
            }
        }

        $this->command?->info(
            "Menu products seeded: {$created} created, {$updated} updated, {$updatedWithImage} updated (image preserved), {$skippedCategories} categories missing.",
        );
    }
}
