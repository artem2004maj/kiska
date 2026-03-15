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
        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->id('plan_id');
            $table->integer('planned_quantity');
            $table->integer('agreed_price');
            $table->integer('is_completed');
            $table->foreignId('record_id')->constrained('medical_records', 'record_id');
            $table->foreignId('service_id')->constrained('services', 'service_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_plans');
    }
};
