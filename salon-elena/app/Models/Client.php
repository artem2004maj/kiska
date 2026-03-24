<?php
// app/Models/Client.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Client extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    protected $table = 'clients';
    protected $primaryKey = 'client_id';

    protected $fillable = [
        'client_name',
        'phone',
        'photo',
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

    // Мутатор для телефона - форматируем при сохранении
    public function setPhoneAttribute($value)
    {
        // Очищаем от всех нецифровых символов
        $cleanPhone = preg_replace('/[^0-9]/', '', $value);
        
        // Если номер начинается с 8, заменяем на +7
        if (strlen($cleanPhone) === 11 && substr($cleanPhone, 0, 1) === '8') {
            $cleanPhone = '7' . substr($cleanPhone, 1);
        }
        
        // Если номер начинается с 7, добавляем +
        if (strlen($cleanPhone) === 11 && substr($cleanPhone, 0, 1) === '7') {
            $this->attributes['phone'] = '+' . $cleanPhone;
        } else {
            $this->attributes['phone'] = $value;
        }
    }

    // Аксессор для форматированного телефона
    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) return '';
        
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        
        if (strlen($phone) === 11) {
            return '+' . substr($phone, 0, 1) . ' ' . substr($phone, 1, 3) . ' ' . substr($phone, 4, 3) . '-' . substr($phone, 7, 2) . '-' . substr($phone, 9, 2);
        }
        
        return $this->phone;
    }

    // Валидация телефона
    public static function validatePhone($phone)
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        return preg_match('/^[7][0-9]{10}$/', $cleanPhone);
    }

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

    // ========== МЕТОДЫ ДЛЯ РАБОТЫ С ФОТО ==========
    
    public function getPhotoUrlAttribute()
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }

    public function hasPhoto()
    {
        return !is_null($this->photo);
    }

    public function deletePhoto()
    {
        if ($this->photo) {
            Storage::disk('public')->delete($this->photo);
            $this->photo = null;
            $this->save();
        }
    }

    // ========== СВЯЗИ ==========
    
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

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'client_id', 'client_id');
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

    public function cancelledAppointments()
    {
        return $this->hasMany(Appointment::class, 'client_id', 'client_id')
                    ->where('status', 3);
    }

    // ========== ДОПОЛНИТЕЛЬНЫЕ МЕТОДЫ ==========
    
    public function getInitialsAttribute()
    {
        $parts = explode(' ', $this->client_name);
        $initials = '';
        foreach ($parts as $part) {
            if (!empty($part)) {
                $initials .= mb_strtoupper(mb_substr($part, 0, 1));
            }
        }
        return $initials;
    }

    public function getAgeAttribute()
    {
        return $this->birth_date ? Carbon::parse($this->birth_date)->age : null;
    }

    public function getUnreadNotificationsCountAttribute()
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    public function markAllNotificationsAsRead()
    {
        return $this->notifications()->update(['is_read' => true]);
    }
}