<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceItemFactory> */
    use HasFactory;

    protected $table = 'invoice_items';
    protected $guarded = [];
    public $timestamps = false; // Usually pivot tables or items don't have timestamps unless schema has them.
    // Check schema: schema has created_at timestamp default CURRENT_TIMESTAMP?
    // Let's assume standard behavior or check schema. Step 30 showed created_at but no updated_at for invoice_items?
    // Step 30: `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP. No updated_at.
    // So distinct $timestamps = false; is likely needed, or const CREATED_AT = 'created_at'; const UPDATED_AT = null;

    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category_info()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
