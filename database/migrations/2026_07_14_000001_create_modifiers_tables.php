<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('modifiers')) {
            Schema::create('modifiers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('price', 10, 2)->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('category_modifier')) {
            Schema::create('category_modifier', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('modifier_id');
                $table->primary(['category_id', 'modifier_id']);
                $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
                $table->foreign('modifier_id')->references('id')->on('modifiers')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('sales_invoice_items')) {
            Schema::table('sales_invoice_items', function (Blueprint $table) {
                if (!Schema::hasColumn('sales_invoice_items', 'size')) {
                    $table->string('size', 8)->nullable()->after('barcode');
                }
                if (!Schema::hasColumn('sales_invoice_items', 'modifiers')) {
                    $table->json('modifiers')->nullable()->after('size');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_invoice_items')) {
            Schema::table('sales_invoice_items', function (Blueprint $table) {
                if (Schema::hasColumn('sales_invoice_items', 'modifiers')) {
                    $table->dropColumn('modifiers');
                }
                if (Schema::hasColumn('sales_invoice_items', 'size')) {
                    $table->dropColumn('size');
                }
            });
        }

        Schema::dropIfExists('category_modifier');
        Schema::dropIfExists('modifiers');
    }
};
