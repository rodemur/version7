<?php
// database/migrations/2024_01_01_000010_create_month_pp_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('month_pp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('month_id')->constrained('months')->cascadeOnDelete();
            $table->foreignId('expense_pp_id')->constrained('expenses_pp')->cascadeOnDelete();
            $table->string('name');                              // Копируем на момент создания
            $table->decimal('planned_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->nullable();
            $table->enum('status', ['planned', 'written_off', 'cancelled'])->default('planned');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('month_pp');
    }
};
