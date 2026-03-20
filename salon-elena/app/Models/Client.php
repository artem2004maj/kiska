<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Storage; // ДОБАВЛЕНО

class Client extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    protected $table = 'clients';
    protected $primaryKey = 'client_id';

    protected $fillable = [
        'client_name',
        'phone',
        'photo', // ДОБАВЛЕНО
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

    public function getAuthPassword()
    {
        return $this->passwd;
    }

    public function getEmailForVerification()
    {
        return $this->email;
    }
    
    public function routeNotificationForMail()
    {
        return $this->email;
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    // ========== НОВЫЕ МЕТОДЫ ДЛЯ ФОТО ==========
    
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

    // ========== СУЩЕСТВУЮЩИЕ СВЯЗИ ==========
    
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'client_id', 'client_id');
    }

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'client_id', 'client_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'client_id', 'client_id');
    }

    public function activeAppointments()
    {
        return $this->hasMany(Appointment::class, 'client_id', 'client_id')
                    ->whereIn('status', [0, 1]);
    }

    public function completedAppointments()
    {
        return $this->hasMany(Appointment::class, 'client_id', 'client_id')
                    ->where('status', 2);
    }
}