<?php
// database/migrations/2026_03_21_xxxxxx_add_appointment_id_to_client_contracts.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('client_contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('appointment_id')->nullable()->after('client_id');
            $table->foreign('appointment_id')
                  ->references('appointment_id')
                  ->on('appointments')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('client_contracts', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn('appointment_id');
        });
    }
};