<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // ДОБАВЛЕНО

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

    // ========== НОВЫЕ МЕТОДЫ ДЛЯ РАБОТЫ С РАСПИСАНИЕМ ==========
    
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
}