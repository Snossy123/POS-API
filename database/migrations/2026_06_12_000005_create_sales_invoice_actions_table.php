<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_invoice_actions')) {
            Schema::create('sales_invoice_actions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->string('action', 50);
                $table->unsignedBigInteger('performed_by')->nullable();
                $table->string('performer_type', 50)->nullable();
                $table->text('reason')->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_actions');
    }
};
