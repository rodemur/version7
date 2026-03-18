<?php
// database/migrations/2024_01_01_000011_create_savings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->string('name');
            $table->enum('mode', ['asap', 'by_date', 'no_goal']);
            $table->decimal('target_amount', 15, 2)->nullable(); // NULL для режима no_goal
            $table->decimal('monthly_payment', 15, 2);
            $table->date('target_date')->nullable();             // NULL для режима no_goal
            $table->decimal('accumulated', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings');
    }
};
