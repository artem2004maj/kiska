<?php
// database/migrations/2026_03_22_xxxxxx_add_calculation_data_to_salaries.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->json('calculation_data')->nullable()->after('payment_date');
        });
    }

    public function down()
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn('calculation_data');
        });
    }
};