<?php
// database/migrations/2026_03_22_xxxxxx_create_supplier_materials_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('supplier_materials')) {
            Schema::create('supplier_materials', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_id');
                $table->unsignedBigInteger('material_id');
                $table->decimal('price', 10, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->foreign('supplier_id')
                      ->references('supplier_id')
                      ->on('suppliers')
                      ->onDelete('cascade');
                      
                $table->foreign('material_id')
                      ->references('material_id')
                      ->on('materials')
                      ->onDelete('cascade');
                
                $table->unique(['supplier_id', 'material_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('supplier_materials');
    }
};