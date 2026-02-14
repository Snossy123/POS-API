<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\SalesInvoice;

class SalesInvoiceItem extends Model
{
    /** @use HasFactory<\Database\Factories\SalesInvoiceItemFactory> */
    use HasFactory;

    protected $table = 'sales_invoice_items';
    protected $guarded = [];
    public $timestamps = false; 

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
