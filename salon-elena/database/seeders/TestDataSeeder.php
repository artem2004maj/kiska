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
        
        // 4. СВЯЗИ ВРАЧ-УСЛУГА
        $this->command->info('🔗 Назначаем услуги врачам...');
        $this->assignServicesToDoctors($employees, $services);
        
        // 5. ГРАФИК РАБОТЫ
        $this->command->info('📅 Добавляем график работы...');
        $this->createSchedules($employees);
        
        // 6. МАТЕРИАЛЫ
        $this->command->info('📦 Добавляем материалы...');
        $materials = $this->createMaterials();
        
        // 7. ПОСТАВЩИКИ
        $this->command->info('🏭 Добавляем поставщиков...');
        $suppliers = $this->createSuppliers();
        
        // 8. МАТЕРИАЛЫ ПОСТАВЩИКОВ
        $this->command->info('🔗 Привязываем материалы к поставщикам...');
        $this->assignMaterialsToSuppliers($suppliers, $materials);
        
        // 9. ЗАПИСИ НА ПРИЕМ
        $this->command->info('📅 Добавляем записи на прием...');
        $appointments = $this->createAppointments($clients, $employees, $services);
        
        // 10. ОКАЗАННЫЕ УСЛУГИ
        $this->command->info('💆‍♀️ Добавляем оказанные услуги...');
        $this->createProvidedServices($appointments, $services, $employees);
        
        // 11. МАТЕРИАЛЫ В ПРИЕМАХ
        $this->command->info('🧴 Добавляем использованные материалы...');
        $this->createAppointmentMaterials($appointments, $materials);
        
        // 12. РАСХОД МАТЕРИАЛОВ
        $this->command->info('📊 Добавляем расход материалов...');
        $this->createConsumption($appointments, $materials);
        
        // 13. ЗАКАЗЫ ПОСТАВЩИКАМ
        $this->command->info('🚚 Добавляем заказы поставщикам...');
        $contracts = $this->createSupplierContracts($suppliers, $materials);
        
        // 14. ПОСТУПЛЕНИЯ МАТЕРИАЛОВ
        $this->command->info('📥 Добавляем поступления материалов...');
        $this->createMaterialReceipts($contracts, $materials);
        
        // 15. ЧЕКИ (ДОГОВОРЫ)
        $this->command->info('💰 Добавляем чеки...');
        $this->createClientContracts($appointments, $clients, $employees);
        
        // 16. РАСХОДЫ КОМПАНИИ
        $this->command->info('💸 Добавляем расходы...');
        $this->createExpenses($contracts);
        
        // 17. ЗАРПЛАТЫ
        $this->command->info('💵 Добавляем зарплаты...');
        $this->createSalaries($employees);
        
        // 18. ОТЗЫВЫ
        $this->command->info('⭐ Добавляем отзывы...');
        $this->createFeedbacks($appointments, $clients);
        
        // 19. МЕДИЦИНСКИЕ КАРТЫ
        $this->command->info('📋 Добавляем медицинские карты...');
        $this->createMedicalRecords($appointments, $clients, $employees);
        
        // 20. УВЕДОМЛЕНИЯ
        $this->command->info('🔔 Добавляем уведомления...');
        $this->createNotifications($appointments, $clients);
        
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
                'passwd' => Hash::make('password123'),
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
            'employee_name' => 'Alexander Petrov',
            'role' => 'director',
            'hourly_rate' => null,
            'employee_phone' => '+79001234567',
            'photo' => null,
            'email' => 'director@elena.ru',
            'login' => 'director',
            'passwd' => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $employees['director'] = [
            'employee_id' => $directorId, 
            'name' => 'Alexander Petrov',
            'hourly_rate' => null,
            'email' => 'director@elena.ru'
        ];
        
        // Бухгалтер
        $accountantId = DB::table('employees')->insertGetId([
            'employee_name' => 'Elena Vasilieva',
            'role' => 'accountant',
            'hourly_rate' => 500.00,
            'employee_phone' => '+79001234568',
            'photo' => null,
            'email' => 'accountant@elena.ru',
            'login' => 'accountant',
            'passwd' => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $employees['accountant'] = [
            'employee_id' => $accountantId, 
            'name' => 'Elena Vasilieva',
            'hourly_rate' => 500.00,
            'email' => 'accountant@elena.ru'
        ];
        
        // Врачи
        $doctorNames = [
            'Irina Kravtsova',
            'Maria Ivanova',
            'Anna Sokolova',
            'Olga Morozova',
            'Ekaterina Lebedeva'
        ];
        
        $doctors = [];
        foreach ($doctorNames as $index => $name) {
            $hourlyRate = rand(500, 800);
            $email = strtolower(str_replace(' ', '.', $name)) . '@elena.ru';
            $id = DB::table('employees')->insertGetId([
                'employee_name' => $name,
                'role' => 'doctor',
                'hourly_rate' => $hourlyRate,
                'employee_phone' => '+79' . rand(10, 99) . rand(1000000, 9999999),
                'photo' => null,
                'email' => $email,
                'login' => 'doctor_' . ($index + 1),
                'passwd' => Hash::make('password123'),
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
        
        // Администраторы
        $adminNames = ['Svetlana Nikolaeva', 'Tatiana Vladimirova'];
        $admins = [];
        foreach ($adminNames as $index => $name) {
            $hourlyRate = 400.00;
            $email = strtolower(str_replace(' ', '.', $name)) . '@elena.ru';
            $id = DB::table('employees')->insertGetId([
                'employee_name' => $name,
                'role' => 'admin',
                'hourly_rate' => $hourlyRate,
                'employee_phone' => '+79' . rand(10, 99) . rand(1000000, 9999999),
                'photo' => null,
                'email' => $email,
                'login' => 'admin_' . ($index + 1),
                'passwd' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $admins[] = [
                'employee_id' => $id,
                'name' => $name,
                'hourly_rate' => $hourlyRate,
                'email' => $email
            ];
        }
        $employees['admins'] = $admins;
        
        return $employees;
    }
    
    private function createServices(): array
    {
        $servicesData = [
            ['Botox (1 zone)', 'Injection cosmetology', 9500],
            ['Ultrasonic facial cleansing', 'Facial care', 5200],
            ['SMAS-lifting face', 'Hardware cosmetology', 42000],
            ['RF-lifting face', 'Hardware cosmetology', 15800],
            ['PRX-T33 peeling', 'Facial care', 8900],
            ['Mesotherapy face', 'Injection cosmetology', 6500],
            ['LPG massage', 'Body care', 4200],
            ['Body wrap', 'Body care', 5900],
            ['PRP therapy hair', 'Trichology', 12800],
            ['Mesotherapy hair', 'Trichology', 7800],
            ['Nail extension', 'Hand care', 5000],
            ['Pedicure', 'Body care', 2000],
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
    
    private function assignServicesToDoctors($employees, $services)
    {
        $doctors = $employees['doctors'];
        $servicesCount = count($services);
        
        foreach ($doctors as $doctor) {
            $numServices = rand(3, min(5, $servicesCount));
            $randomServices = array_rand($services, $numServices);
            $randomServices = is_array($randomServices) ? $randomServices : [$randomServices];
            
            foreach ($randomServices as $index) {
                DB::table('doctor_services')->insert([
                    'doctor_id' => $doctor['employee_id'],
                    'service_id' => $services[$index]['service_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function createSchedules($employees)
    {
        $doctors = $employees['doctors'];
        $days = [1, 2, 3, 4, 5, 6]; // Mon-Sat
        
        foreach ($doctors as $doctor) {
            foreach ($days as $day) {
                DB::table('doctor_schedules')->insert([
                    'doctor_id' => $doctor['employee_id'],
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '18:00:00',
                    'slot_duration' => 60,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function createMaterials(): array
    {
        $materialsData = [
            ['Medical gloves', 'pair', 50, 200, 50],
            ['Alcohol wipes', 'pack', 30, 100, 80],
            ['Anesthetic', 'ml', 20, 150, 25],
            ['Hyaluronic acid', 'syringe', 10, 50, 1200],
            ['Botox', 'unit', 100, 500, 15],
            ['Face mask', 'piece', 20, 80, 200],
            ['Serum', 'ml', 15, 60, 150],
            ['Peeling gel', 'ml', 25, 100, 180],
            ['Post-procedure cream', 'tube', 10, 40, 350],
            ['Cotton pads', 'pack', 30, 150, 60],
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
    
    private function createAppointments($clients, $employees, $services): array
    {
        $appointments = [];
        $doctors = $employees['doctors'];
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now()->addDays(30);
        
        for ($i = 1; $i <= 30; $i++) {
            $date = Carbon::createFromTimestamp(rand($startDate->timestamp, $endDate->timestamp));
            $client = $clients[array_rand($clients)];
            $doctor = $doctors[array_rand($doctors)];
            $status = rand(0, 3);
            
            $id = DB::table('appointments')->insertGetId([
                'date' => $date,
                'status' => $status,
                'total_price' => null,
                'employee_id' => $doctor['employee_id'],
                'client_id' => $client['client_id'],
                'created_at' => $date->copy()->subDays(rand(1, 7)),
                'updated_at' => $date,
            ]);
            
            $appointments[] = [
                'appointment_id' => $id,
                'date' => $date,
                'status' => $status,
                'client_id' => $client['client_id'],
                'employee_id' => $doctor['employee_id']
            ];
        }
        
        return $appointments;
    }
    
    private function createProvidedServices($appointments, $services, $employees)
    {
        $servicesCount = count($services);
        
        foreach ($appointments as $appointment) {
            $numServices = rand(1, min(2, $servicesCount));
            $randomServices = array_rand($services, $numServices);
            $randomServices = is_array($randomServices) ? $randomServices : [$randomServices];
            
            foreach ($randomServices as $index) {
                DB::table('provided_services')->insert([
                    'quantity' => 1,
                    'service_date' => Carbon::parse($appointment['date'])->format('Y-m-d'),
                    'notes' => '',
                    'appointment_id' => $appointment['appointment_id'],
                    'service_id' => $services[$index]['service_id'],
                    'employee_id' => $appointment['employee_id'],
                    'created_at' => $appointment['date'],
                    'updated_at' => $appointment['date'],
                ]);
            }
        }
    }
    
    private function createAppointmentMaterials($appointments, $materials)
    {
        $materialsCount = count($materials);
        
        foreach ($appointments as $appointment) {
            if ($appointment['status'] == 2) {
                $numMaterials = rand(0, 3);
                
                if ($numMaterials == 0) {
                    continue;
                }
                
                $numMaterials = min($numMaterials, $materialsCount);
                $randomMaterials = array_rand($materials, $numMaterials);
                if (!is_array($randomMaterials)) {
                    $randomMaterials = [$randomMaterials];
                }
                
                foreach ($randomMaterials as $index) {
                    $quantity = rand(1, 3);
                    DB::table('appointment_materials')->insert([
                        'appointment_id' => $appointment['appointment_id'],
                        'material_id' => $materials[$index]['material_id'],
                        'quantity_used' => $quantity,
                        'cost_price' => $materials[$index]['price_per_unit'],
                        'notes' => null,
                        'created_at' => $appointment['date'],
                        'updated_at' => $appointment['date'],
                    ]);
                }
            }
        }
    }
    
    private function createConsumption($appointments, $materials)
    {
        foreach ($appointments as $appointment) {
            $appointmentMaterials = DB::table('appointment_materials')
                ->where('appointment_id', $appointment['appointment_id'])
                ->get();
            
            foreach ($appointmentMaterials as $am) {
                DB::table('consumption')->insert([
                    'batch_number' => 'APT-' . $appointment['appointment_id'] . '-' . time() . '-' . $am->material_id,
                    'quantity' => $am->quantity_used,
                    'cost_price' => $am->cost_price,
                    'provided_id' => null,
                    'material_id' => $am->material_id,
                    'created_at' => $appointment['date'],
                    'updated_at' => $appointment['date'],
                ]);
            }
        }
    }
    
    private function createSupplierContracts($suppliers, $materials): array
    {
        $contracts = [];
        
        foreach ($suppliers as $supplier) {
            for ($i = 1; $i <= 3; $i++) {
                $status = rand(0, 2);
                $contractNumber = 'PO-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                $id = DB::table('supplier_contracts')->insertGetId([
                    'number' => $contractNumber,
                    'date' => Carbon::now()->subDays(rand(1, 30)),
                    'status' => $status,
                    'confirmed_at' => $status >= 1 ? Carbon::now()->subDays(rand(1, 20)) : null,
                    'received_at' => $status == 2 ? Carbon::now()->subDays(rand(1, 10)) : null,
                    'valid_from' => Carbon::now()->subDays(30),
                    'valid_to' => Carbon::now()->addDays(30),
                    'supplier_id' => $supplier['supplier_id'],
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                    'updated_at' => now(),
                ]);
                
                $contracts[] = [
                    'contract_id' => $id,
                    'supplier_id' => $supplier['supplier_id'],
                    'status' => $status
                ];
            }
        }
        
        return $contracts;
    }
    
    private function createMaterialReceipts($contracts, $materials)
    {
        $materialsCount = count($materials);
        
        foreach ($contracts as $contract) {
            if ($contract['status'] == 2) {
                $numMaterials = rand(1, min(3, $materialsCount));
                $randomMaterials = array_rand($materials, $numMaterials);
                $randomMaterials = is_array($randomMaterials) ? $randomMaterials : [$randomMaterials];
                
                foreach ($randomMaterials as $index) {
                    $quantity = rand(5, 50);
                    DB::table('material_receipts')->insert([
                        'quantity' => $quantity,
                        'price' => $materials[$index]['price_per_unit'] * 0.8,
                        'batch_number' => time() + rand(1, 10000),
                        'expiry_date' => Carbon::now()->addYears(1),
                        'receipt_date' => Carbon::now()->subDays(rand(1, 10)),
                        'invoice_number' => 'INV-' . time() . rand(1000, 9999),
                        'status' => 1,
                        'material_id' => $materials[$index]['material_id'],
                        'contract_id' => $contract['contract_id'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
    
    private function createClientContracts($appointments, $clients, $employees)
    {
        foreach ($appointments as $appointment) {
            if ($appointment['status'] == 2) {
                $totalPrice = DB::table('provided_services')
                    ->where('appointment_id', $appointment['appointment_id'])
                    ->join('services', 'provided_services.service_id', '=', 'services.service_id')
                    ->sum('services.default_price');
                
                $materialTotal = DB::table('appointment_materials')
                    ->where('appointment_id', $appointment['appointment_id'])
                    ->sum(DB::raw('quantity_used * cost_price'));
                
                $totalAmount = $totalPrice + $materialTotal;
                
                if ($totalAmount > 0) {
                    DB::table('client_contracts')->insert([
                        'contract_date' => Carbon::parse($appointment['date'])->format('Y-m-d'),
                        'status' => 1,
                        'contract_number' => 'INV-' . date('Ymd') . '-' . str_pad($appointment['appointment_id'], 6, '0', STR_PAD_LEFT),
                        'total_amount' => $totalAmount,
                        'signed_at' => Carbon::parse($appointment['date'])->format('Y-m-d'),
                        'employee_id' => $appointment['employee_id'],
                        'client_id' => $appointment['client_id'],
                        'appointment_id' => $appointment['appointment_id'],
                        'created_at' => $appointment['date'],
                        'updated_at' => $appointment['date'],
                    ]);
                }
            }
        }
    }
    
    private function createExpenses($contracts)
    {
        foreach ($contracts as $contract) {
            if ($contract['status'] == 1) {
                $totalAmount = DB::table('material_receipts')
                    ->where('contract_id', $contract['contract_id'])
                    ->sum(DB::raw('quantity * price'));
                
                if ($totalAmount > 0) {
                    DB::table('expenses')->insert([
                        'type' => 'supplier_order',
                        'amount' => $totalAmount,
                        'date' => Carbon::now()->subDays(rand(1, 15)),
                        'description' => 'Order from supplier',
                        'reference_id' => $contract['contract_id'],
                        'reference_type' => 'App\\Models\\SupplierContract',
                        'metadata' => json_encode(['order_id' => $contract['contract_id']]),
                        'is_confirmed' => 1,
                        'confirmed_at' => Carbon::now()->subDays(rand(1, 15)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
    
    private function createFeedbacks($appointments, $clients)
    {
        $comments = [
            'Excellent specialist!',
            'Very satisfied with the result!',
            'Everything went great, thank you!',
            'Highly recommend!',
            'Professional and high quality!',
            'Will definitely come again!',
            'Thank you for your work!',
            'Best salon in the city!',
        ];
        
        foreach ($appointments as $appointment) {
            if ($appointment['status'] == 2 && rand(1, 100) <= 70) {
                DB::table('feedback')->insert([
                    'score' => rand(4, 5),
                    'comment' => $comments[array_rand($comments)],
                    'client_id' => $appointment['client_id'],
                    'appointment_id' => $appointment['appointment_id'],
                    'created_at' => $appointment['date'],
                    'updated_at' => $appointment['date'],
                ]);
            }
        }
    }
    
    private function createMedicalRecords($appointments, $clients, $employees)
    {
        foreach ($appointments as $appointment) {
            if ($appointment['status'] == 2 && rand(1, 100) <= 50) {
                DB::table('medical_records')->insert([
                    'visit_date' => Carbon::parse($appointment['date'])->format('Y-m-d'),
                    'anamnesis' => 'No complaints, preventive examination',
                    'diagnosis' => 'Cosmetic correction',
                    'contraindications' => 'None identified',
                    'employee_id' => $appointment['employee_id'],
                    'client_id' => $appointment['client_id'],
                    'appointment_id' => $appointment['appointment_id'],
                    'created_at' => $appointment['date'],
                    'updated_at' => $appointment['date'],
                ]);
            }
        }
    }
    
    private function createNotifications($appointments, $clients)
    {
        foreach ($appointments as $appointment) {
            if ($appointment['status'] == 1) {
                DB::table('notifications')->insert([
                    'client_id' => $appointment['client_id'],
                    'appointment_id' => $appointment['appointment_id'],
                    'type' => 'confirmation',
                    'message' => '✅ Your appointment has been confirmed! We are waiting for you.',
                    'is_read' => rand(0, 1),
                    'created_at' => $appointment['date'],
                    'updated_at' => $appointment['date'],
                ]);
            }
            
            if ($appointment['status'] == 2) {
                DB::table('notifications')->insert([
                    'client_id' => $appointment['client_id'],
                    'appointment_id' => $appointment['appointment_id'],
                    'type' => 'completion',
                    'message' => '💫 Appointment completed. Thank you for your visit!',
                    'is_read' => rand(0, 1),
                    'created_at' => $appointment['date'],
                    'updated_at' => $appointment['date'],
                ]);
            }
        }
    }
        private function createSalaries($employees)
    {
        $doctors = $employees['doctors'];
        
        foreach ($doctors as $doctor) {
            for ($month = 1; $month <= 3; $month++) {
                $hoursWorked = rand(80, 160);
                $totalAmount = $doctor['hourly_rate'] * $hoursWorked;
                $ndfl = round($totalAmount * 0.13, 2);
                $netSalary = $totalAmount - $ndfl;
                
                DB::table('salaries')->insert([
                    'employee_id' => $doctor['employee_id'],
                    'month' => $month,
                    'year' => 2026,
                    'hours_worked' => $hoursWorked,
                    'hourly_rate' => $doctor['hourly_rate'],
                    'total_amount' => $netSalary,
                    'is_paid' => $month < 3 ? 1 : 0,
                    'payment_date' => $month < 3 ? Carbon::create(2026, $month, 15) : null,
                    'calculation_data' => json_encode([
                        'base_salary' => $totalAmount,
                        'ndfl' => $ndfl,
                        'net_salary' => $netSalary
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}