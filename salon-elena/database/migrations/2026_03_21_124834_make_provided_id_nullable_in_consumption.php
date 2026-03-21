<?php
// database/migrations/2026_03_21_xxxxxx_make_provided_id_nullable_in_consumption.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('consumption', function (Blueprint $table) {
            $table->unsignedBigInteger('provided_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('consumption', function (Blueprint $table) {
            $table->unsignedBigInteger('provided_id')->nullable(false)->change();
        });
    }
};