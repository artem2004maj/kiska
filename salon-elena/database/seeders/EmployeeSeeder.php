<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = [
            [
                'employee_name' => 'Елена Александровна Кравцова',
                'role'          => 'doctor',
                'employee_phone'=> '963842340',
                'email'         => 'elena.kravtsova@elena.ru',
                'login'         => 'lalalend',
                'passwd'        => Hash::make('doctor2026'),
            ],
            [
                'employee_name' => 'Мария Сергеевна Иванова',
                'role'          => 'doctor',
                'employee_phone'=> '963842341',
                'email'         => 'maria.ivanova@elena.ru',
                'login'         => 'maria',
                'passwd'        => Hash::make('doctor2026'),
            ],
            [
                'employee_name' => 'Анна Дмитриевна Соколова',
                'role'          => 'doctor',
                'employee_phone'=> '963842345',
                'email'         => 'anna.sokolova@elena.ru',
                'login'         => 'anna',
                'passwd'        => Hash::make('doctor2026'),
            ],
            [
                'employee_name' => 'Ольга Павловна Морозова',
                'role'          => 'doctor',
                'employee_phone'=> '963842349',
                'email'         => 'olga.morozova@elena.ru',
                'login'         => 'olga',
                'passwd'        => Hash::make('doctor2026'),
            ],
            [
                'employee_name' => 'Екатерина Викторовна Лебедева',
                'role'          => 'doctor',
                'employee_phone'=> '963842341',
                'email'         => 'admin@elena.ru',
                'login'         => 'ecatca',
                'passwd'        => Hash::make('doctor2026'),
            ],
        ];

        foreach ($doctors as $data) {
            Employee::create($data);
        }
    }
}