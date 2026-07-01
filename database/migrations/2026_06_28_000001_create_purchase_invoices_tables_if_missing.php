<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_invoices')) {
            Schema::create('purchase_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number');
                $table->string('supplier');
                $table->date('date');
                $table->time('time');
                $table->decimal('total', 12, 2);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('product_name');
                $table->string('barcode')->nullable();
                $table->decimal('quantity', 12, 2);
                $table->decimal('purchase_price', 12, 2);
                $table->decimal('sale_price', 12, 2);
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('invoice_id')->references('id')->on('purchase_invoices')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('purchase_invoices');
    }
};
