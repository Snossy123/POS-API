<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_items', 'product_id')) {
                $table->integer('product_id')->nullable()->after('invoice_id');
            }
            if (!Schema::hasColumn('invoice_items', 'category_id')) {
                $table->integer('category_id')->nullable()->after('product_id');
            }
        });

        Schema::table('sales_invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoice_items', 'product_id')) {
                $table->integer('product_id')->nullable()->after('invoice_id');
            }
        });

        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role', 50);
                $table->string('phone', 20)->nullable();
                $table->decimal('salary', 10, 2)->nullable();
                $table->date('hiring_date')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['product_id', 'category_id']);
        });
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->dropColumn('product_id');
        });
    }
};
