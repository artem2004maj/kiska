<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Конвертируем телефоны клиентов
        DB::table('clients')->whereNotNull('phone')->where('phone', '>', 0)->get()
            ->each(function($client) {
                $phone = (string) $client->phone;
                if (strlen($phone) === 11 && $phone[0] === '8') {
                    $phone = '7' . substr($phone, 1);
                }
                if (strlen($phone) === 11 && $phone[0] === '7') {
                    $phone = '+' . $phone;
                }
                DB::table('clients')->where('client_id', $client->client_id)
                    ->update(['phone' => $phone]);
            });
        
        // Конвертируем телефоны сотрудников
        DB::table('employees')->whereNotNull('employee_phone')->where('employee_phone', '>', 0)->get()
            ->each(function($employee) {
                $phone = (string) $employee->employee_phone;
                if (strlen($phone) === 11 && $phone[0] === '8') {
                    $phone = '7' . substr($phone, 1);
                }
                if (strlen($phone) === 11 && $phone[0] === '7') {
                    $phone = '+' . $phone;
                }
                DB::table('employees')->where('employee_id', $employee->employee_id)
                    ->update(['employee_phone' => $phone]);
            });
    }
};
