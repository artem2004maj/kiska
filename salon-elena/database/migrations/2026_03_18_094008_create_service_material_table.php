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
        Schema::create('service_material', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('material_id');
            $table->integer('quantity')->default(1);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            
            // Внешние ключи
            $table->foreign('service_id')
                  ->references('service_id')
                  ->on('services')
                  ->onDelete('cascade');
                  
            $table->foreign('material_id')
                  ->references('material_id')
                  ->on('materials')
                  ->onDelete('cascade');
            
            // Уникальность пары (чтобы не было дублей)
            $table->unique(['service_id', 'material_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_material');
    }
};