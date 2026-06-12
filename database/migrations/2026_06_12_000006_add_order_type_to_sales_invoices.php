<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_invoices')) {
            return;
        }

        if (!Schema::hasColumn('sales_invoices', 'order_type')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->string('order_type', 20)->default('takeaway')->after('kitchen_note');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_invoices') && Schema::hasColumn('sales_invoices', 'order_type')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->dropColumn('order_type');
            });
        }
    }
};
