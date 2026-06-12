<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shifts')) {
            Schema::create('shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->timestamp('opened_at');
                $table->timestamp('closed_at')->nullable();
                $table->decimal('opening_float', 12, 2)->default(0);
                $table->decimal('expected_cash', 12, 2)->nullable();
                $table->decimal('actual_cash', 12, 2)->nullable();
                $table->decimal('cash_difference', 12, 2)->nullable();
                $table->string('status', 20)->default('open');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
