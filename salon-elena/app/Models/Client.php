<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class Client extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    protected $table = 'clients';
    protected $primaryKey = 'client_id';

    protected $fillable = [
        'client_name',
        'phone',
        'email',
        'birth_date',
        'login',
        'passwd',
    ];

    protected $hidden = [
        'passwd',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
    ];

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    // Laravel по умолчанию ищет поле 'password', а у нас 'passwd'
    public function getAuthPassword()
    {
        return $this->passwd;
    }

    // Для верификации email
    public function getEmailForVerification()
    {
        return $this->email;
    }
    
    // Для отправки уведомлений (например, сброс пароля)
    public function routeNotificationForMail()
    {
        return $this->email;
    }

    public function getAuthIdentifier()
    {
        return $this->getKey(); // возвращает client_id
    }

    // ========== ДОБАВЛЕННЫЕ СВЯЗИ ==========
    
    /**
     * Все записи (приемы) клиента
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'client_id', 'client_id');
    }

    /**
     * Все отзывы клиента
     */
    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'client_id', 'client_id');
    }

    /**
     * Все записи в медицинской карте клиента
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'client_id', 'client_id');
    }

    /**
     * Активные записи (не завершенные и не отмененные)
     */
    public function activeAppointments()
    {
        return $this->hasMany(Appointment::class, 'client_id', 'client_id')
                    ->whereIn('status', [0, 1]); // 0 - запланирован, 1 - подтвержден
    }

    /**
     * Завершенные записи
     */
    public function completedAppointments()
    {
        return $this->hasMany(Appointment::class, 'client_id', 'client_id')
                    ->where('status', 2); // 2 - завершен
    }
}