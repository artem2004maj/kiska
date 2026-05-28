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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            
            // Основные реквизиты
            $table->string('company_name')->default('Косметологическая клиника');
            $table->string('short_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            
            // Юридические реквизиты
            $table->string('inn')->nullable();              // ИНН
            $table->string('kpp')->nullable();              // КПП
            $table->string('ogrn')->nullable();             // ОГРН
            $table->string('okpo')->nullable();             // ОКПО
            
            // Адреса и контакты
            $table->text('legal_address')->nullable();      // Юридический адрес
            $table->text('actual_address')->nullable();     // Фактический адрес
            $table->string('phone')->nullable();            // Телефон
            $table->string('email')->nullable();            // Email
            $table->string('website')->nullable();          // Сайт
            
            // Руководство
            $table->string('director_name')->nullable();    // Генеральный директор
            $table->string('accountant_name')->nullable();  // Главный бухгалтер
            
            // Банковские реквизиты (JSON для гибкости)
            $table->json('bank_details')->nullable();       // [{bank_name, bik, correspondent_account, payment_account}]
            
            // Документы
            $table->text('document_header')->nullable();    // Текст в шапку документов
            $table->text('document_footer')->nullable();    // Текст в подвал документов
            $table->string('stamp_path')->nullable();       // Изображение печати
            
            // Социальные сети
            $table->string('instagram')->nullable();
            $table->string('telegram')->nullable();
            $table->string('vk')->nullable();
            
            // Дополнительные настройки
            $table->json('additional')->nullable();         // Запасной JSON для будущих полей
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};