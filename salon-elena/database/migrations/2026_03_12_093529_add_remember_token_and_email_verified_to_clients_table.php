<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Добавляем remember_token (нужен для функции "запомнить меня")
            $table->rememberToken()->after('passwd');
            
            // Добавляем email_verified_at (для верификации email)
            $table->timestamp('email_verified_at')->nullable()->after('email');
            
            // Убедимся, что email уникальный
            $table->unique('email', 'clients_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('remember_token');
            $table->dropColumn('email_verified_at');
            $table->dropUnique('clients_email_unique');
        });
    }
};