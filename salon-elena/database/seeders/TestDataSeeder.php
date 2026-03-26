<?php
// database/seeders/TestDataSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Начинаем заполнение тестовыми данными...');
        
        // 1. КЛИЕНТЫ
        $this->command->info('📝 Добавляем клиентов...');
        $clients = $this->createClients();
        
        // 2. СОТРУДНИКИ
        $this->command->info('👥 Добавляем сотрудников...');
        $employees = $this->createEmployees();
        
        // 3. УСЛУГИ
        $this->command->info('💅 Добавляем услуги...');
        $services = $this->createServices();
        
        // 6. МАТЕРИАЛЫ
        $this->command->info('📦 Добавляем материалы...');
        $materials = $this->createMaterials();
        
        // 7. ПОСТАВЩИКИ
        $this->command->info('🏭 Добавляем поставщиков...');
        $suppliers = $this->createSuppliers();
        
        // 8. МАТЕРИАЛЫ ПОСТАВЩИКОВ
        $this->command->info('🔗 Привязываем материалы к поставщикам...');
        $this->assignMaterialsToSuppliers($suppliers, $materials);
        
        
        
        $this->command->info('✅ Тестовые данные успешно загружены!');
    }
    
    private function createClients(): array
    {
        $clients = [];
        $clientNames = [
            'Anna Petrova', 'Maria Ivanova', 'Ekaterina Smirnova', 
            'Olga Kuznetsova', 'Tatiana Popova', 'Natalia Sokolova',
            'Irina Lebedeva', 'Svetlana Kozlova', 'Elena Novikova'
        ];
        
        foreach ($clientNames as $index => $name) {
            $id = DB::table('clients')->insertGetId([
                'client_name' => $name,
                'phone' => '+7' . rand(900, 999) . rand(1000000, 9999999),
                'photo' => null,
                'email' => strtolower(str_replace(' ', '', $name)) . '@example.com',
                'email_verified_at' => now(),
                'birth_date' => Carbon::now()->subYears(rand(20, 50))->subDays(rand(0, 365)),
                'login' => 'client_' . ($index + 1),
                'passwd' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $clients[] = [
                'client_id' => $id, 
                'client_name' => $name,
                'email' => strtolower(str_replace(' ', '', $name)) . '@example.com'
            ];
        }
        
        return $clients;
    }
    
    private function createEmployees(): array
    {
        $employees = [];
        
        // Директор
        $directorId = DB::table('employees')->insertGetId([
            'employee_name' => 'Алексадр Петров',
            'role' => 'director',
            'hourly_rate' => null,
            'employee_phone' => '+79001234567',
            'photo' => null,
            'email' => 'director@example.ru',
            'login' => 'director',
            'passwd' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $employees['director'] = [
            'employee_id' => $directorId, 
            'name' => 'Александр Петров',
            'hourly_rate' => null,
            'email' => 'director@example.ru'
        ];
        
        // Бухгалтер
        $accountantId = DB::table('employees')->insertGetId([
            'employee_name' => 'Елена Васильева',
            'role' => 'accountant',
            'hourly_rate' => 500.00,
            'employee_phone' => '+79001234568',
            'photo' => null,
            'email' => 'accountant@example.ru',
            'login' => 'accountant',
            'passwd' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $employees['accountant'] = [
            'employee_id' => $accountantId, 
            'name' => 'Елена Васильева',
            'hourly_rate' => 500.00,
            'email' => 'accountant@example.ru'
        ];
        
        // Врачи
        $doctorNames = [
            'Ирина Кравцова',
            'Мария Иванова',
        ];
        
        $doctors = [];
        foreach ($doctorNames as $index => $name) {
            $hourlyRate = rand(500, 800);
            $email = strtolower(str_replace(' ', '.', $name)) . '@example.ru';
            $id = DB::table('employees')->insertGetId([
                'employee_name' => $name,
                'role' => 'doctor',
                'hourly_rate' => $hourlyRate,
                'employee_phone' => '+79' . rand(10, 99) . rand(1000000, 9999999),
                'photo' => null,
                'email' => $email,
                'login' => 'doctor_' . ($index + 1),
                'passwd' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $doctors[] = [
                'employee_id' => $id,
                'name' => $name,
                'hourly_rate' => $hourlyRate,
                'email' => $email
            ];
        }
        $employees['doctors'] = $doctors;
        
        return $employees;
    }
    
    private function createServices(): array
    {
        $servicesData = [
            ['Ботокс (1 зона)', 'Инъекционная косметология', 9500],
            ['Ультразвуковая чистка лица', 'Уход за лицом', 5200],
            ['СМАС-лифтинг лица', 'Аппаратная косметология', 42000],
            ['RF-лифтинг лица', 'Аппаратная косметология', 15800],
            ['Пилинг PRX-T33', 'Уход за лицом', 8900],
            ['Мезотерапия лица', 'Инъекционная косметология', 6500],
            ['LPG-массаж', 'Уход за телом', 4200],
            ['Обертывание тела', 'Уход за телом', 5900],
            ['PRP-терапия волос', 'Трихология', 12800],
            ['Мезотерапия волос', 'Трихология', 7800],
            ['Наращивание ногтей', 'Уход за руками', 5000],
            ['Педикюр', 'Уход за телом', 2000],
        ];
        
        $services = [];
        foreach ($servicesData as $data) {
            $id = DB::table('services')->insertGetId([
                'service_name' => $data[0],
                'service_category' => $data[1],
                'default_price' => $data[2],
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $services[] = [
                'service_id' => $id,
                'service_name' => $data[0],
                'service_category' => $data[1],
                'default_price' => $data[2]
            ];
        }
        
        return $services;
    }
    
    private function createMaterials(): array
    {
        $materialsData = [
            ['Медицинские перчатки', 'пара', 50, 200, 50],
            ['Спиртовые салфетки', 'упаковка', 30, 100, 80],
            ['Анестетик', 'мл', 20, 150, 25],
            ['Гиалуроновая кислота', 'шприц', 10, 50, 1200],
            ['Ботокс', 'единица', 100, 500, 15],
            ['Маска для лица', 'штука', 20, 80, 200],
            ['Сыворотка', 'мл', 15, 60, 150],
            ['Гель для пилинга', 'мл', 25, 100, 180],
            ['Постпроцедурный крем', 'туба', 10, 40, 350],
            ['Ватные диски', 'упаковка', 30, 150, 60],
        ];
        
        $materials = [];
        foreach ($materialsData as $data) {
            $id = DB::table('materials')->insertGetId([
                'name' => $data[0],
                'unit' => $data[1],
                'min_stock' => $data[2],
                'current_balance' => $data[3],
                'price_per_unit' => $data[4],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $materials[] = [
                'material_id' => $id,
                'name' => $data[0],
                'unit' => $data[1],
                'price_per_unit' => $data[4]
            ];
        }
        
        return $materials;
    }
    
    private function createSuppliers(): array
    {
        $suppliersData = [
            ['MedSupply LLC', 'Ivan Ivanov', '+79001234567', 'medsupply@example.com', 'Moscow', '1234567890', 'Sberbank', '044525225', '40702810123456789012'],
            ['CosmetLine LLC', 'Petr Petrov', '+79007654321', 'cosmetline@example.com', 'Saint Petersburg', '9876543210', 'Alfa-Bank', '044525593', '40702810765432109876'],
            ['HealthPlus', 'Anna Sidorova', '+79009876543', 'healthplus@example.com', 'Novosibirsk', '1122334455', 'Tinkoff', '044525974', '40817810123456789012'],
        ];
        
        $suppliers = [];
        foreach ($suppliersData as $data) {
            $id = DB::table('suppliers')->insertGetId([
                'supplier_name' => $data[0],
                'contact_person' => $data[1],
                'phone' => $data[2],
                'email' => $data[3],
                'address' => $data[4],
                'notes' => null,
                'inn' => $data[5],
                'director_fio' => $data[1],
                'accountant_fio' => null,
                'bank_name' => $data[6],
                'bic' => $data[7],
                'payment_account' => $data[8],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $suppliers[] = [
                'supplier_id' => $id,
                'supplier_name' => $data[0]
            ];
        }
        
        return $suppliers;
    }
    
    private function assignMaterialsToSuppliers($suppliers, $materials)
    {
        $materialsCount = count($materials);
        
        foreach ($suppliers as $supplier) {
            $numMaterials = rand(3, min(5, $materialsCount));
            $randomMaterials = array_rand($materials, $numMaterials);
            $randomMaterials = is_array($randomMaterials) ? $randomMaterials : [$randomMaterials];
            
            foreach ($randomMaterials as $index) {
                DB::table('supplier_materials')->insert([
                    'supplier_id' => $supplier['supplier_id'],
                    'material_id' => $materials[$index]['material_id'],
                    'price' => $materials[$index]['price_per_unit'] * (0.7 + rand(0, 30) / 100),
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}