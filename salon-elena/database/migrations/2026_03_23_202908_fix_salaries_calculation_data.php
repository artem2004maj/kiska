<?php
// database/migrations/2026_03_23_xxxxxx_fix_salaries_calculation_data.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $salaries = DB::table('salaries')->get();
        
        foreach ($salaries as $salary) {
            $data = $salary->calculation_data;
            
            if ($data && is_string($data)) {
                // Пробуем декодировать
                $decoded = json_decode($data, true);
                
                // Если после первого декодирования получили строку, декодируем еще раз
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }
                
                if ($decoded && is_array($decoded)) {
                    DB::table('salaries')
                        ->where('salary_id', $salary->salary_id)
                        ->update(['calculation_data' => json_encode($decoded)]);
                }
            }
        }
    }
    
    public function down()
    {
        // Откат не требуется
    }
};