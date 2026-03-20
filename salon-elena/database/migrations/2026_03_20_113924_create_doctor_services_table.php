<?php
// database/migrations/2026_03_20_xxxxxx_create_doctor_services_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('doctor_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('service_id');
            $table->timestamps();
            
            $table->foreign('doctor_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
            $table->unique(['doctor_id', 'service_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_services');
    }
};