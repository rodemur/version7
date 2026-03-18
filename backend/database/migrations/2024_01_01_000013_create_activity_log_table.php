<?php
// database/migrations/2024_01_01_000013_create_activity_log_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 100);         // write_off, cancel, confirm, close_month ...
            $table->string('entity_type', 100);    // month_oep, month_pp, month_saving, month_income ...
            $table->unsignedBigInteger('entity_id');
            $table->json('payload')->nullable();   // { old: {...}, new: {...} }
            $table->string('description')->nullable(); // Человекочитаемое описание
            $table->timestamp('created_at')->useCurrent(); // Только created_at, без updated_at
            // Лог только добавляется, никогда не редактируется

            // Индексы для быстрой выборки последних 50 действий
            $table->index(['budget_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
