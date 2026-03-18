<?php
// database/migrations/2024_01_01_000003_create_budget_user_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['owner', 'member'])->default('member');
            $table->timestamps();

            $table->unique(['budget_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_user');
    }
};
