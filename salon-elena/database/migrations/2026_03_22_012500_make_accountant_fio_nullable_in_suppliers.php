<?php
// database/migrations/2026_03_22_xxxxxx_make_accountant_fio_nullable_in_suppliers.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('accountant_fio')->nullable()->change();
            $table->string('director_fio')->nullable()->change();
            $table->string('inn')->nullable()->change();
            $table->string('bank_name')->nullable()->change();
            $table->string('bic')->nullable()->change();
            $table->string('payment_account')->nullable()->change();
            $table->integer('delivery_days')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('accountant_fio')->nullable(false)->change();
            $table->string('director_fio')->nullable(false)->change();
            $table->string('inn')->nullable(false)->change();
            $table->string('bank_name')->nullable(false)->change();
            $table->string('bic')->nullable(false)->change();
            $table->string('payment_account')->nullable(false)->change();
            $table->integer('delivery_days')->nullable(false)->change();
        });
    }
};