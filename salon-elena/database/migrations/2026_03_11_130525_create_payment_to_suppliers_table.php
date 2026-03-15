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
        Schema::create('payment_to_suppliers', function (Blueprint $table) {
            $table->id('payment_id');
            $table->integer('amount');
            $table->date('payment_date');
            $table->integer('status');
            $table->foreignId('contract_id')->constrained('supplier_contracts', 'contract_id');
            $table->foreignId('receipt_id')->constrained('material_receipts', 'receipt_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_to_suppliers');
    }
};
