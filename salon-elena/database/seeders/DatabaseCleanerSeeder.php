<?php
// database/seeders/DatabaseCleanerSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseCleanerSeeder extends Seeder
{
    /**
     * Очистка всех таблиц от данных
     */
    public function run(): void
    {
        // Отключаем проверку внешних ключей
        Schema::disableForeignKeyConstraints();
        
        // Список таблиц в правильном порядке удаления
        $tables = [
            'notifications',
            'feedback',
            'medical_records',
            'appointment_materials',
            'consumption',
            'provided_services',
            'appointments',
            'client_contracts',
            'salaries',
            'expenses',
            'material_receipts',
            'supplier_contracts',
            'supplier_materials',
            'doctor_services',
            'doctor_schedules',
            'materials',
            'services',
            'suppliers',
            'employees',
            'clients',
        ];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->command->info("Таблица {$table} очищена");
            }
        }
        
        // Включаем проверку внешних ключей
        Schema::enableForeignKeyConstraints();
        
        $this->command->info('✅ Все таблицы успешно очищены!');
    }
}