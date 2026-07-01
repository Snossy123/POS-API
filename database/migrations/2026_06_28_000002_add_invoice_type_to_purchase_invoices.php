<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_invoices') && !Schema::hasColumn('purchase_invoices', 'invoice_type')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                $table->string('invoice_type', 20)->default('general')->after('supplier');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase_invoices', 'invoice_type')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                $table->dropColumn('invoice_type');
            });
        }
    }
};
