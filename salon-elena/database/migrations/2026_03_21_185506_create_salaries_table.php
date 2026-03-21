<?php
// database/migrations/2026_03_22_xxxxxx_create_salaries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id('salary_id'); // Явно указываем имя первичного ключа
            $table->unsignedBigInteger('employee_id');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->boolean('is_paid')->default(false);
            $table->date('payment_date')->nullable();
            $table->timestamps();
            
            // Внешний ключ с явным указанием поля employee_id
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');
            
            // Уникальность: один сотрудник, один месяц, один год
            $table->unique(['employee_id', 'month', 'year'], 'salaries_employee_month_year_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('salaries');
    }
};