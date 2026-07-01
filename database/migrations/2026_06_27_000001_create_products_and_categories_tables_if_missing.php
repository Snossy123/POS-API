<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('color', 20)->default('#000000');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('hasSizes')->default(false);
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('s_price', 10, 2)->default(0);
                $table->decimal('m_price', 10, 2)->default(0);
                $table->decimal('l_price', 10, 2)->default(0);
                $table->integer('stock')->default(0);
                $table->string('barcode')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('image')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
