<?php
// database/migrations/2026_03_22_xxxxxx_add_hourly_rate_to_employees.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('role');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('hourly_rate');
        });
    }
};