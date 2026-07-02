<?php

namespace App\Services;

use App\Models\Product;

class InventoryService
{
    public function deductForSale(array $items): void
    {
        foreach ($this->aggregateQuantitiesByProductId($items) as $productId => $quantity) {
            Product::where('id', $productId)->decrement('stock', $quantity);
        }
    }

    public function addForPurchase(array $items): void
    {
        foreach ($this->aggregateQuantitiesByProductId($items) as $productId => $quantity) {
            Product::where('id', $productId)->increment('stock', $quantity);
        }
    }

    public function adjustStock(int $productId, float $delta): void
    {
        if ($delta == 0) {
            return;
        }

        if ($delta > 0) {
            Product::where('id', $productId)->increment('stock', $delta);
            return;
        }

        Product::where('id', $productId)->decrement('stock', abs($delta));
    }

    public function restoreForInvoiceItems(iterable $items): void
    {
        foreach ($items as $item) {
            if (!$item->product_id) {
                continue;
            }

            Product::where('id', $item->product_id)
                ->increment('stock', (float) $item->quantity);
        }
    }

    public function restorePartial(iterable $items, array $refundItems): void
    {
        $refundMap = collect($refundItems)->mapWithKeys(function ($item) {
            $productId = $item['product_id'] ?? $item['id'] ?? null;
            $name = $item['name'] ?? $item['product_name'] ?? '';
            $key = $productId ? (string) $productId : $name;

            return [$key => (float) ($item['quantity'] ?? 0)];
        });

        foreach ($items as $item) {
            if (!$item->product_id) {
                continue;
            }

            $refundQty = (float) ($refundMap->get((string) $item->product_id) ?? $refundMap->get($item->product_name) ?? 0);
            if ($refundQty <= 0) {
                continue;
            }

            Product::where('id', $item->product_id)
                ->increment('stock', $refundQty);
        }
    }

    public function adjustForItemChanges(iterable $oldItems, array $newItems): void
    {
        $oldMap = [];
        foreach ($oldItems as $item) {
            if (!$item->product_id) {
                continue;
            }
            $key = (string) $item->product_id;
            $oldMap[$key] = ($oldMap[$key] ?? 0) + (float) $item->quantity;
        }

        $newMap = $this->aggregateQuantitiesByProductId($newItems);

        $allKeys = array_unique(array_merge(array_keys($oldMap), array_keys($newMap)));

        foreach ($allKeys as $key) {
            $oldQty = $oldMap[$key] ?? 0;
            $newQty = $newMap[$key] ?? 0;
            $delta = $newQty - $oldQty;

            if ($delta > 0) {
                Product::where('id', $key)->decrement('stock', $delta);
            } elseif ($delta < 0) {
                Product::where('id', $key)->increment('stock', abs($delta));
            }
        }
    }

    /**
     * @return array<string, float>
     */
    private function aggregateQuantitiesByProductId(array $items): array
    {
        $aggregated = [];

        foreach ($items as $item) {
            $productId = $item['product_id'] ?? $item['id'] ?? null;
            $quantity = (float) ($item['quantity'] ?? 0);

            if (!$productId || $quantity <= 0) {
                continue;
            }

            $key = (string) $productId;
            $aggregated[$key] = ($aggregated[$key] ?? 0) + $quantity;
        }

        return $aggregated;
    }
}
