<?php
// database/migrations/2026_03_21_000000_create_doctor_schedules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->tinyInteger('day_of_week'); // 0 = воскресенье, 1 = понедельник, ... 6 = суббота
            $table->time('start_time')->nullable(); // null = выходной
            $table->time('end_time')->nullable();
            $table->integer('slot_duration')->default(60); // длительность слота в минутах
            $table->timestamps();
            
            $table->foreign('doctor_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->unique(['doctor_id', 'day_of_week']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_schedules');
    }
};