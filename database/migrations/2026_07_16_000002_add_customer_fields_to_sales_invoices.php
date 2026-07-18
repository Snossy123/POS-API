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

        if (!Schema::hasColumn('sales_invoices', 'customer_id')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('employee_id');
            });
        }

        if (!Schema::hasColumn('sales_invoices', 'customer_name')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->string('customer_name')->nullable()->after('order_type');
            });
        }

        if (!Schema::hasColumn('sales_invoices', 'customer_phone')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->string('customer_phone')->nullable()->after('customer_name');
            });
        }

        if (!Schema::hasColumn('sales_invoices', 'customer_address')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->text('customer_address')->nullable()->after('customer_phone');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sales_invoices')) {
            return;
        }

        Schema::table('sales_invoices', function (Blueprint $table) {
            $columns = [];
            foreach (['customer_id', 'customer_name', 'customer_phone', 'customer_address'] as $column) {
                if (Schema::hasColumn('sales_invoices', $column)) {
                    $columns[] = $column;
                }
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
