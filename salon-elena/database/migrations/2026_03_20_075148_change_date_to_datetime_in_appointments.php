<?php
// database/migrations/2026_03_20_xxxxxx_change_date_to_datetime_in_appointments.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // MySQL 8.0 поддерживает изменение типа с date на datetime
            $table->dateTime('date')->change();
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->date('date')->change();
        });
    }
};