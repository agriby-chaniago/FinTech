<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financial_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('total_income');
            $table->unsignedBigInteger('total_expense');
            $table->bigInteger('saving_amount');
            $table->decimal('saving_percentage', 5, 2)->default(20.00);
            $table->unsignedBigInteger('saving_plan');
            $table->string('top_category')->nullable();
            $table->text('insight')->nullable();
            $table->string('investment_recommendation');
            $table->enum('risk_level', ['low', 'medium', 'high']);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_plans');
    }
};
