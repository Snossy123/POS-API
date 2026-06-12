<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SalesInvoiceItem;

class InventoryService
{
    public function deductForSale(array $items): void
    {
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $quantity = (float) ($item['quantity'] ?? 0);

            if (!$productId || $quantity <= 0) {
                continue;
            }

            Product::where('id', $productId)
                ->where('stock', '>=', $quantity)
                ->decrement('stock', $quantity);
        }
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
        $refundMap = collect($refundItems)->keyBy(fn ($item) => $item['name'] ?? $item['product_name'] ?? '');

        foreach ($items as $item) {
            if (!$item->product_id) {
                continue;
            }

            $refundQty = (float) ($refundMap->get($item->product_name)['quantity'] ?? 0);
            if ($refundQty <= 0) {
                continue;
            }

            Product::where('id', $item->product_id)
                ->increment('stock', $refundQty);
        }
    }
}
