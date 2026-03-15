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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id('record_id');
            $table->date('visit_date');
            $table->string('anamnesis', 256);
            $table->string('diagnosis', 256);
            $table->string('contraindications', 256);
            $table->foreignId('employee_id')->constrained('employees', 'employee_id');
            $table->foreignId('client_id')->constrained('clients', 'client_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
