<?php
// database/migrations/2024_01_01_000009_create_expenses_pp_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses_pp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('target_amount', 15, 2);            // Целевая сумма
            $table->decimal('monthly_payment', 15, 2);          // target_amount / period_months
            $table->unsignedInteger('period_months');           // Периодичность в месяцах
            $table->date('target_date');                        // Текущая дата платежа
            $table->date('next_target_date')->nullable();       // Следующая дата (после списания)
            $table->decimal('accumulated', 15, 2)->default(0);  // Уже накоплено
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses_pp');
    }
};
