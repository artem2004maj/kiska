<?php
// database/migrations/2026_03_18_xxxxxx_create_appointment_materials_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_materials', function (Blueprint $table) {
            $table->id('appointment_material_id');
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('material_id');
            $table->integer('quantity_used')->default(1); // Сколько материала использовано
            $table->decimal('cost_price', 10, 2)->nullable(); // Цена за единицу на момент использования
            $table->text('notes')->nullable(); // Примечания
            $table->timestamps();
            
            // Внешние ключи
            $table->foreign('appointment_id')
                  ->references('appointment_id')
                  ->on('appointments')
                  ->onDelete('cascade');
                  
            $table->foreign('material_id')
                  ->references('material_id')
                  ->on('materials')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_materials');
    }
};