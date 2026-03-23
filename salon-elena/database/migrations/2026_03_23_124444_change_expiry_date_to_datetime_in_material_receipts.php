<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_receipts', function (Blueprint $table) {
            // Сначала проверяем, существует ли поле
            if (Schema::hasColumn('material_receipts', 'expiry_date')) {
                // Меняем тип с integer на datetime
                $table->dateTime('expiry_date')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('material_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('material_receipts', 'expiry_date')) {
                $table->integer('expiry_date')->nullable()->change();
            }
        });
    }
};