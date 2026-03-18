<?php
// database/migrations/2024_01_01_000012_create_month_savings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('month_savings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('month_id')->constrained('months')->cascadeOnDelete();
            $table->foreignId('saving_id')->constrained('savings')->cascadeOnDelete();
            $table->string('name');                              // Копируем на момент создания
            $table->decimal('planned_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->nullable();
            $table->enum('status', ['planned', 'reserved', 'written_off', 'cancelled'])
                  ->default('planned');
            // planned    — запланирован, ещё не подтверждён
            // reserved   — взнос подтверждён, деньги зарезервированы (ВС не изменился)
            // written_off — нажата кнопка «Потратить», деньги списаны с ВС
            // cancelled  — платёж отменён
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('month_savings');
    }
};
