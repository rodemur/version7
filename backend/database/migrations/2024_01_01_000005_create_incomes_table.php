<?php
// database/migrations/2024_01_01_000005_create_incomes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['permanent', 'temporary', 'one_time'])->default('permanent');
            $table->decimal('amount', 15, 2);
            $table->date('starts_at')->nullable();   // Для временных доходов
            $table->date('ends_at')->nullable();     // Для временных доходов
            $table->boolean('is_paused')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
