<?php
// database/migrations/2026_03_21_xxxxxx_add_price_fields_to_appointments_and_materials.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Добавляем поле total_price в appointments
        Schema::table('appointments', function (Blueprint $table) {
            $table->decimal('total_price', 10, 2)->nullable()->after('status');
        });
        
        // Добавляем поле price_per_unit в materials
        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('price_per_unit', 10, 2)->nullable()->after('current_balance');
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('total_price');
        });
        
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('price_per_unit');
        });
    }
};