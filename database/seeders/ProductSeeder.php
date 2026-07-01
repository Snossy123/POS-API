<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    private const ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp'];

    /**
     * Seed products from images in public/products_images/.
     */
    public function run(): void
    {
        $imagesDir = public_path('products_images');

        if (!File::isDirectory($imagesDir)) {
            $this->command?->error("Directory not found: {$imagesDir}");

            return;
        }

        $created = 0;
        $updated = 0;

        foreach (File::files($imagesDir) as $file) {
            $filename = $file->getFilename();

            if (str_starts_with($filename, '.')) {
                continue;
            }

            if (!in_array(strtolower($file->getExtension()), self::ALLOWED_EXTENSIONS, true)) {
                continue;
            }

            $name = pathinfo($filename, PATHINFO_FILENAME);

            $product = Product::updateOrCreate(
                ['name' => $name],
                [
                    'hasSizes' => 1,
                    'price' => 0,
                    's_price' => 0,
                    'm_price' => 0,
                    'l_price' => 0,
                    'stock' => 0,
                    'barcode' => null,
                    'image' => 'products_images/' . $filename,
                ],
            );

            $product->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->command?->info("Products seeded: {$created} created, {$updated} updated.");
    }
}
