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
        Schema::create('clients', function (Blueprint $table) {
            $table->id('client_id');
            $table->string('client_name', 100);
            $table->integer('phone');
            $table->string('email', 100);
            $table->date('birth_date');
            $table->string('login', 56)->unique();
            $table->string('passwd', 56);
            $table->timestamps();  // Не добавляем, т.к. created_at уже есть
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
