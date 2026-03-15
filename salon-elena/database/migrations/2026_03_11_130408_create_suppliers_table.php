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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id('supplier_id');
            $table->string('supplier_name', 100);
            $table->integer('inn');
            $table->string('director_fio', 100);
            $table->string('accountant_fio', 100);
            $table->string('bank_name', 100);
            $table->string('bic', 100);
            $table->string('payment_account', 100);
            $table->integer('delivery_days');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
