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
        Schema::create('client_contracts', function (Blueprint $table) {
            $table->id('contract_id');
            $table->date('contract_date');
            $table->integer('status');
            $table->string('contract_number', 100);
            $table->integer('total_amount');
            $table->date('signed_at');
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
        Schema::dropIfExists('client_contracts');
    }
};
