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

        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoices', 'shift_id')) {
                $table->unsignedBigInteger('shift_id')->nullable()->after('employee_id');
            }
            if (!Schema::hasColumn('sales_invoices', 'payment_method')) {
                $table->string('payment_method', 20)->default('cash')->after('total');
            }
            if (!Schema::hasColumn('sales_invoices', 'amount_paid')) {
                $table->decimal('amount_paid', 12, 2)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('sales_invoices', 'change_given')) {
                $table->decimal('change_given', 12, 2)->nullable()->after('amount_paid');
            }
            if (!Schema::hasColumn('sales_invoices', 'status')) {
                $table->string('status', 30)->default('completed')->after('kitchen_note');
            }
            if (!Schema::hasColumn('sales_invoices', 'payment_status')) {
                $table->string('payment_status', 20)->default('paid')->after('status');
            }
            if (!Schema::hasColumn('sales_invoices', 'voided_at')) {
                $table->timestamp('voided_at')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('sales_invoices', 'voided_by')) {
                $table->unsignedBigInteger('voided_by')->nullable()->after('voided_at');
            }
            if (!Schema::hasColumn('sales_invoices', 'refund_amount')) {
                $table->decimal('refund_amount', 12, 2)->default(0)->after('voided_by');
            }
            if (!Schema::hasColumn('sales_invoices', 'parent_invoice_id')) {
                $table->unsignedBigInteger('parent_invoice_id')->nullable()->after('refund_amount');
            }
            if (!Schema::hasColumn('sales_invoices', 'client_id')) {
                $table->string('client_id', 64)->nullable()->unique()->after('parent_invoice_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('sales_invoices')) {
            return;
        }

        Schema::table('sales_invoices', function (Blueprint $table) {
            $columns = [
                'shift_id', 'payment_method', 'amount_paid', 'change_given',
                'status', 'payment_status', 'voided_at', 'voided_by',
                'refund_amount', 'parent_invoice_id', 'client_id',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('sales_invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
