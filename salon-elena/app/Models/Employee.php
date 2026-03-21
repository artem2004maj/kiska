<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $table = 'employees';
    protected $primaryKey = 'employee_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_name',
        'role',
        'hourly_rate',
        'employee_phone',
        'photo',
        'email',
        'login',
        'passwd',
    ];

    protected $hidden = [
        'passwd',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'hourly_rate' => 'decimal:2',
    ];

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'employee_id';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->employee_id;
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->passwd;
    }

    /**
     * Get the remember token for the user.
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the remember token for the user.
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the remember token.
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    // Mutator для email
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    // ========== МЕТОДЫ ДЛЯ РАБОТЫ С ФОТО ==========
    
    /**
     * Аксессор для получения полного URL фото
     */
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }

    /**
     * Проверка наличия фото
     */
    public function hasPhoto()
    {
        return !is_null($this->photo);
    }

    /**
     * Удаление фото
     */
    public function deletePhoto()
    {
        if ($this->photo) {
            Storage::disk('public')->delete($this->photo);
            $this->photo = null;
            $this->save();
        }
    }

    // ========== СВЯЗИ ==========
    
    public function services()
    {
        return $this->belongsToMany(Service::class, 'doctor_services', 'doctor_id', 'service_id')
                    ->withTimestamps();
    }
    
    public function clientContracts()
    {
        return $this->hasMany(ClientContract::class, 'employee_id', 'employee_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'employee_id', 'employee_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'employee_id', 'employee_id');
    }

    public function providedServices()
    {
        return $this->hasMany(ProvidedService::class, 'employee_id', 'employee_id');
    }

    // ========== СВЯЗЬ С ЗАРПЛАТОЙ ==========
    
    /**
     * Связь с записями о зарплате
     */
    public function salaries()
    {
        return $this->hasMany(Salary::class, 'employee_id', 'employee_id');
    }

    // ========== МЕТОДЫ ДЛЯ РАБОТЫ С РАСПИСАНИЕМ ==========
    
    /**
     * Связь с расписанием работы врача
     */
    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id', 'employee_id');
    }

    /**
     * Получить расписание на конкретный день недели
     * 
     * @param int $dayOfWeek 0-6 (0 = воскресенье, 1 = понедельник, ...)
     * @return \App\Models\DoctorSchedule|null
     */
    public function getScheduleForDay($dayOfWeek)
    {
        return $this->schedules()->where('day_of_week', $dayOfWeek)->first();
    }

    /**
     * Получить рабочие часы для конкретной даты
     * 
     * @param \Carbon\Carbon|string $date
     * @return array Массив временных слотов (например: ['09:00', '10:00', ...])
     */
    public function getWorkingHoursForDate($date)
    {
        $date = Carbon::parse($date);
        $dayOfWeek = $date->dayOfWeek;
        $schedule = $this->getScheduleForDay($dayOfWeek);
        
        if (!$schedule || !$schedule->start_time || !$schedule->end_time) {
            return [];
        }
        
        $hours = [];
        $start = Carbon::parse($schedule->start_time);
        $end = Carbon::parse($schedule->end_time);
        
        while ($start < $end) {
            $hours[] = $start->format('H:i');
            $start->addMinutes($schedule->slot_duration);
        }
        
        return $hours;
    }

    /**
     * Проверить, работает ли врач в указанный день
     * 
     * @param \Carbon\Carbon|string $date
     * @return bool
     */
    public function isWorkingOnDate($date)
    {
        $date = Carbon::parse($date);
        $schedule = $this->getScheduleForDay($date->dayOfWeek);
        
        return $schedule && $schedule->start_time && $schedule->end_time;
    }

    /**
     * Проверить, работает ли врач в указанное время
     * 
     * @param \Carbon\Carbon|string $dateTime
     * @return bool
     */
    public function isWorkingAtDateTime($dateTime)
    {
        $dateTime = Carbon::parse($dateTime);
        $schedule = $this->getScheduleForDay($dateTime->dayOfWeek);
        
        if (!$schedule || !$schedule->start_time || !$schedule->end_time) {
            return false;
        }
        
        $start = Carbon::parse($schedule->start_time);
        $end = Carbon::parse($schedule->end_time);
        $time = Carbon::parse($dateTime->format('H:i:s'));
        
        return $time->between($start, $end);
    }

    /**
     * Получить расписание на неделю
     * 
     * @return array
     */
    public function getWeeklySchedule()
    {
        $schedule = [];
        $days = [1, 2, 3, 4, 5, 6, 0]; // Пн, Вт, Ср, Чт, Пт, Сб, Вс
        
        foreach ($days as $day) {
            $daySchedule = $this->getScheduleForDay($day);
            if ($daySchedule && $daySchedule->start_time && $daySchedule->end_time) {
                $schedule[$day] = [
                    'start' => Carbon::parse($daySchedule->start_time)->format('H:i'),
                    'end' => Carbon::parse($daySchedule->end_time)->format('H:i'),
                    'slot_duration' => $daySchedule->slot_duration,
                    'working' => true
                ];
            } else {
                $schedule[$day] = [
                    'working' => false
                ];
            }
        }
        
        return $schedule;
    }
    
    // ========== МЕТОДЫ ДЛЯ РАСЧЕТА ЗАРПЛАТЫ ==========
    
    /**
     * Константы типов оплаты
     */
    const PAYMENT_TYPE_HOURLY = 'hourly';  // Почасовая оплата
    const PAYMENT_TYPE_DAILY = 'daily';    // Дневная оплата

    /**
     * Получить зарплату за месяц с учетом типа оплаты
     * 
     * @param int $month
     * @param int $year
     * @param string $paymentType
     * @param float $hoursOrDays
     * @param float $bonus
     * @return array
     */
    public function calculateSalary($month, $year, $paymentType, $hoursOrDays, $bonus = 0)
    {
        $hourlyRate = $this->hourly_rate ?? 0;
        
        if ($paymentType === self::PAYMENT_TYPE_HOURLY) {
            $baseSalary = $hourlyRate * $hoursOrDays;
        } else {
            // Для дневной оплаты считаем 8-часовой рабочий день
            $dailyRate = $hourlyRate * 8;
            $baseSalary = $dailyRate * $hoursOrDays;
        }
        
        $totalAccrued = $baseSalary + $bonus;
        $ndfl = round($totalAccrued * 0.13, 2); // НДФЛ 13%
        $netSalary = $totalAccrued - $ndfl;
        
        return [
            'base_salary' => $baseSalary,
            'bonus' => $bonus,
            'total_accrued' => $totalAccrued,
            'ndfl' => $ndfl,
            'net_salary' => $netSalary,
            'hourly_rate' => $hourlyRate,
            'payment_type' => $paymentType,
            'hours_or_days' => $hoursOrDays,
        ];
    }

    /**
     * Сохранить расчет зарплаты в таблицу salaries
     */
    public function saveSalaryCalculation($month, $year, $paymentType, $hoursOrDays, $bonus = 0)
    {
        $calculation = $this->calculateSalary($month, $year, $paymentType, $hoursOrDays, $bonus);
        
        return $this->salaries()->updateOrCreate(
            [
                'month' => $month,
                'year' => $year,
            ],
            [
                'hours_worked' => $paymentType === self::PAYMENT_TYPE_HOURLY ? $hoursOrDays : $hoursOrDays * 8,
                'hourly_rate' => $this->hourly_rate ?? 0,
                'total_amount' => $calculation['net_salary'],
                'is_paid' => false,
                'payment_date' => null,
                'calculation_data' => json_encode($calculation), // Сохраняем полные данные расчета
            ]
        );
    }




    /**
     * Рассчитать отработанные часы за период (на основе расписания, а не записей)
     * 
     * @param \Carbon\Carbon|string $startDate
     * @param \Carbon\Carbon|string $endDate
     * @return float
     */
    public function calculateWorkedHours($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $totalHours = 0;
        
        $current = $start->copy();
        
        while ($current <= $end) {
            $dayOfWeek = $current->dayOfWeek;
            $schedule = $this->getScheduleForDay($dayOfWeek);
            
            if ($schedule && $schedule->start_time && $schedule->end_time) {
                $startHour = Carbon::parse($schedule->start_time)->hour;
                $endHour = Carbon::parse($schedule->end_time)->hour;
                $startMinute = Carbon::parse($schedule->start_time)->minute;
                $endMinute = Carbon::parse($schedule->end_time)->minute;
                
                $hoursThisDay = ($endHour - $startHour) + (($endMinute - $startMinute) / 60);
                $totalHours += $hoursThisDay;
            }
            
            $current->addDay();
        }
        
        return $totalHours;
    }

    /**
     * Рассчитать зарплату за период
     * 
     * @param \Carbon\Carbon|string $startDate
     * @param \Carbon\Carbon|string $endDate
     * @return float
     */
    public function calculateSalaryForPeriod($startDate, $endDate)
    {
        $hoursWorked = $this->calculateWorkedHours($startDate, $endDate);
        $rate = $this->hourly_rate ?? 0;
        
        return $hoursWorked * $rate;
    }

    /**
     * Получить зарплату за месяц
     * 
     * @param int $month
     * @param int $year
     * @return object
     */
    public function getSalaryForMonth($month, $year)
    {
        $salary = $this->salaries()
            ->where('month', $month)
            ->where('year', $year)
            ->first();
        
        if ($salary) {
            return $salary;
        }
        
        // Если нет записи, рассчитываем
        $startDate = Carbon::create($year, $month, 1);
        $endDate = Carbon::create($year, $month, $startDate->daysInMonth);
        
        $hoursWorked = $this->calculateWorkedHours($startDate, $endDate);
        $totalAmount = $hoursWorked * ($this->hourly_rate ?? 0);
        
        return (object) [
            'hours_worked' => $hoursWorked,
            'total_amount' => $totalAmount,
            'is_paid' => false,
        ];
    }

    /**
     * Сохранить зарплату за месяц
     * 
     * @param int $month
     * @param int $year
     * @return \App\Models\Salary
     */
    public function saveSalaryForMonth($month, $year)
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = Carbon::create($year, $month, $startDate->daysInMonth);
        
        $hoursWorked = $this->calculateWorkedHours($startDate, $endDate);
        $totalAmount = $hoursWorked * ($this->hourly_rate ?? 0);
        
        return $this->salaries()->updateOrCreate(
            [
                'month' => $month,
                'year' => $year,
            ],
            [
                'hours_worked' => $hoursWorked,
                'hourly_rate' => $this->hourly_rate ?? 0,
                'total_amount' => $totalAmount,
                'is_paid' => false,
            ]
        );
    }

    // ========== ДОПОЛНИТЕЛЬНЫЕ МЕТОДЫ ==========
    
    /**
     * Получить имя врача для отображения
     */
    public function getFullNameAttribute()
    {
        return $this->employee_name;
    }
    
    /**
     * Проверить, является ли сотрудник доктором
     */
    public function isDoctor()
    {
        return $this->role === 'doctor';
    }
    
    /**
     * Проверить, является ли сотрудник администратором
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    /**
     * Проверить, является ли сотрудник бухгалтером
     */
    public function isAccountant()
    {
        return $this->role === 'accountant';
    }
    
    /**
     * Проверить, является ли сотрудник директором
     */
    public function isDirector()
    {
        return $this->role === 'director';
    }

    /**
     * Получить отработанные часы на основе завершенных приемов (альтернативный метод)
     * 
     * @param \Carbon\Carbon|string $startDate
     * @param \Carbon\Carbon|string $endDate
     * @return int
     */
    public function getWorkedHoursFromAppointments($startDate, $endDate)
    {
        return $this->appointments()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 2)
            ->count();
    }

    /**
     * Получить отработанные часы с учетом длительности приемов
     * 
     * @param \Carbon\Carbon|string $startDate
     * @param \Carbon\Carbon|string $endDate
     * @return float
     */
    public function getWorkedHoursWithDuration($startDate, $endDate)
    {
        $appointments = $this->appointments()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 2)
            ->get();
        
        $totalHours = 0;
        foreach ($appointments as $appointment) {
            // Предполагаем, что каждый прием длится 1 час
            $totalHours += 1;
        }
        
        return $totalHours;
    }
}