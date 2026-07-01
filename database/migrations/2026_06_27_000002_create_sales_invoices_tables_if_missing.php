<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_invoices')) {
            Schema::create('sales_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number');
                $table->date('date');
                $table->time('time');
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->unsignedBigInteger('shift_id')->nullable();
                $table->decimal('total', 12, 2);
                $table->string('payment_method', 20)->default('cash');
                $table->decimal('amount_paid', 12, 2)->nullable();
                $table->decimal('change_given', 12, 2)->nullable();
                $table->text('kitchen_note')->nullable();
                $table->string('order_type', 20)->default('takeaway');
                $table->string('status', 30)->default('completed');
                $table->string('payment_status', 20)->default('paid');
                $table->timestamp('voided_at')->nullable();
                $table->unsignedBigInteger('voided_by')->nullable();
                $table->decimal('refund_amount', 12, 2)->default(0);
                $table->unsignedBigInteger('parent_invoice_id')->nullable();
                $table->string('client_id', 64)->nullable()->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sales_invoice_items')) {
            Schema::create('sales_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_name');
                $table->decimal('price', 12, 2);
                $table->integer('quantity');
                $table->string('barcode')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
    }
};
