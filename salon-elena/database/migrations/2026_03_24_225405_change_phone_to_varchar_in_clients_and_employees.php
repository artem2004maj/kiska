<?php
// database/migrations/xxxx_xx_xx_xxxxxx_change_phone_to_varchar_in_clients_and_employees.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Меняем тип поля phone в таблице clients
        Schema::table('clients', function (Blueprint $table) {
            $table->string('phone', 20)->change();
        });
        
        // Меняем тип поля employee_phone в таблице employees
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_phone', 20)->nullable()->change();
        });
    }

    public function down()
    {
        // Возвращаем обратно (если нужно)
        Schema::table('clients', function (Blueprint $table) {
            $table->integer('phone')->change();
        });
        
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('employee_phone')->change();
        });
    }
};