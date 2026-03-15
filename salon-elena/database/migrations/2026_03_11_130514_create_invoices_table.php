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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->integer('amount');
            $table->date('issued_at');
            $table->date('due_date');
            $table->date('paid_at');
            $table->string('method', 100);
            $table->integer('status');
            $table->foreignId('contract_id')->constrained('client_contracts', 'contract_id');
            $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
