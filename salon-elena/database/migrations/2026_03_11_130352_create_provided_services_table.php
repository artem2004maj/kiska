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
        Schema::create('provided_services', function (Blueprint $table) {
            $table->id('provided_id');
            $table->integer('quantity');
            $table->date('service_date');
            $table->string('notes', 256);
            $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id');
            $table->foreignId('service_id')->constrained('services', 'service_id');
            $table->foreignId('employee_id')->constrained('employees', 'employee_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provided_services');
    }
};
