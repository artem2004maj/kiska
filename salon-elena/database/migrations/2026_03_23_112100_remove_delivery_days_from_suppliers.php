<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'delivery_days')) {
                $table->dropColumn('delivery_days');
            }
        });
    }

    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->integer('delivery_days')->nullable()->after('payment_account');
        });
    }
};