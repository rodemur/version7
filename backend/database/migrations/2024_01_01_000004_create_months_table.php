<?php
// database/migrations/2024_01_01_000004_create_months_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->date('period');                              // Первый день месяца: 2025-03-01
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->decimal('carried_over_balance', 15, 2)->default(0); // Перенесённый СО
            $table->decimal('virtual_account', 15, 2)->default(0);      // Виртуальный счёт
            $table->decimal('reserved_funds', 15, 2)->default(0);       // Зарезервированные средства
            $table->decimal('free_balance', 15, 2)->default(0);         // Свободный остаток
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            // Один месяц на бюджет
            $table->unique(['budget_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('months');
    }
};
