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
        Schema::create('employees', function (Blueprint $table) {
            $table->id('employee_id');  // INT NOT NULL PRIMARY KEY
            $table->string('employee_name', 100);
            $table->string('role', 100);  // Для ролей из ТЗ: 'admin', 'doctor', etc.
            $table->integer('employee_phone');
            $table->string('employee_email', 100);
            $table->string('login', 56)->unique();
            $table->string('passwd', 56);  // Будем хэшировать в модели
            $table->timestamps();  // Добавим для удобства (created_at/updated_at)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
