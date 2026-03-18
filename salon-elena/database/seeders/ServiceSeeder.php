<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            // Инъекционная косметология
            [
                'service_name'     => 'Биоревитализация лица (гиалуроновая кислота 2 мл)',
                'service_category' => 'Инъекционная косметология',
                'default_price'    => 14800,
                'is_active'        => 1,
            ],
            [
                'service_name'     => 'Контурная пластика губ (1 мл)',
                'service_category' => 'Инъекционная косметология',
                'default_price'    => 17900,
                'is_active'        => 1,
            ],
            [
                'service_name'     => 'Ботокс / Диспорт (1 зона)',
                'service_category' => 'Инъекционная косметология',
                'default_price'    => 9500,
                'is_active'        => 1,
            ],
            [
                'service_name'     => 'Мезотерапия лица (витаминный коктейль)',
                'service_category' => 'Инъекционная косметология',
                'default_price'    => 6500,
                'is_active'        => 1,
            ],

            // Аппаратные методики
            [
                'service_name'     => 'SMAS-лифтинг Ulthera / Doublo (лицо + шея)',
                'service_category' => 'Аппаратная косметология',
                'default_price'    => 42000,
                'is_active'        => 1,
            ],
            [
                'service_name'     => 'RF-лифтинг лица',
                'service_category' => 'Аппаратная косметология',
                'default_price'    => 15800,
                'is_active'        => 1,
            ],
            [
                'service_name'     => 'HydraFacial — Deluxe (с пилингом + сыворотками)',
                'service_category' => 'Аппаратная косметология',
                'default_price'    => 9900,
                'is_active'        => 1,
            ],

            // Уходовые процедуры для лица
            [
                'service_name'     => 'Ультразвуковая чистка + комбинированная чистка',
                'service_category' => 'Уход за лицом',
                'default_price'    => 5200,
                'is_active'        => 1,
            ],
            [
                'service_name'     => 'Программа anti-age «Глубокое увлажнение + лифтинг»',
                'service_category' => 'Уход за лицом',
                'default_price'    => 6800,
                'is_active'        => 1,
            ],
            [
                'service_name'     => 'Пилинг PRX-T33 (без реабилитации)',
                'service_category' => 'Уход за лицом',
                'default_price'    => 8900,
                'is_active'        => 1,
            ],

            // Трихология и волосы
            [
                'service_name'     => 'PRP-терапия волосистой части головы (1 пробирка)',
                'service_category' => 'Трихология',
                'default_price'    => 12800,
                'is_active'        => 1,
            ],
            [
                'service_name'     => 'Мезотерапия волос Dr.CYJ / Mesopecia',
                'service_category' => 'Трихология',
                'default_price'    => 7800,
                'is_active'        => 1,
            ],

            // Уход за телом
            [
                'service_name'     => 'LPG массаж (1 зона 40 мин)',
                'service_category' => 'Уход за телом',
                'default_price'    => 4200,
                'is_active'        => 1,
            ],
            [
                'service_name'     => 'Обертывание «Детокс + лимфодренаж»',
                'service_category' => 'Уход за телом',
                'default_price'    => 5900,
                'is_active'        => 1,
            ],
        ];

        foreach ($services as $serviceData) {
            Service::create($serviceData);
        }

        // Если хочешь добавить несколько неактивных услуг для теста:
        // Service::create([
        //     'service_name'     => 'Лазерная эпиляция подмышек (пробная)',
        //     'service_category' => 'Удаление волос',
        //     'default_price'    => 2500,
        //     'is_active'        => 0,
        // ]);
    }
}