<?php
// database/migrations/2024_01_01_000006_create_month_incomes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('month_incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('month_id')->constrained('months')->cascadeOnDelete();
            $table->foreignId('income_id')->nullable()->constrained('incomes')->nullOnDelete();
            // income_id = NULL для разовых доходов без шаблона
            $table->string('name');                             // Копируем название на момент создания
            $table->decimal('planned_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->nullable(); // Заполняется при подтверждении
            $table->enum('status', ['planned', 'confirmed'])->default('planned');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('month_incomes');
    }
};
