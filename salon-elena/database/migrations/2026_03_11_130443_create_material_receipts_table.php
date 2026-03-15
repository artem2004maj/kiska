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
        Schema::create('material_receipts', function (Blueprint $table) {
            $table->id('receipt_id');
            $table->integer('quantity');
            $table->integer('price');
            $table->integer('batch_number');
            $table->integer('expiry_date');
            $table->date('receipt_date');
            $table->string('invoice_number', 100);
            $table->integer('status');
            $table->foreignId('material_id')->constrained('materials', 'material_id');
            $table->foreignId('contract_id')->constrained('supplier_contracts', 'contract_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_receipts');
    }
};
