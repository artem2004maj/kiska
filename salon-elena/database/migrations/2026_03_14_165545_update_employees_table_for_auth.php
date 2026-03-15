<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Увеличиваем длину пароля
            $table->string('passwd', 255)->change();
            
            // Добавляем remember_token, если нет
            if (!Schema::hasColumn('employees', 'remember_token')) {
                $table->rememberToken();
            }
            
            // Добавляем email_verified_at, если нет
            if (!Schema::hasColumn('employees', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('passwd', 56)->change();
            $table->dropColumn(['remember_token', 'email_verified_at']);
        });
    }
};