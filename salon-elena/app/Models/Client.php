<?php

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
     * Все уведомления клиента
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'client_id', 'client_id');
    }

    /**
     * Активные записи (не завершенные и не отмененные)
     */
    public function activeAppointments()
    {
        return $this->hasMany(Appointment::class, 'client_id', 'client_id')
                    ->whereIn('status', [0, 1]);
    }

    /**
     * Завершенные записи
     */
    public function completedAppointments()
    {
        return $this->hasMany(Appointment::class, 'client_id', 'client_id')
                    ->where('status', 2);
    }

    /**
     * Отмененные записи
     */
    public function cancelledAppointments()
    {
        return $this->hasMany(Appointment::class, 'client_id', 'client_id')
                    ->where('status', 3);
    }

    // ========== ДОПОЛНИТЕЛЬНЫЕ МЕТОДЫ ==========
    
    /**
     * Получить инициалы клиента
     */
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

    /**
     * Получить возраст клиента
     */
    public function getAgeAttribute()
    {
        return $this->birth_date ? Carbon::parse($this->birth_date)->age : null;
    }

    /**
     * Получить количество непрочитанных уведомлений
     */
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    /**
     * Отметить все уведомления как прочитанные
     */
    public function markAllNotificationsAsRead()
    {
        return $this->notifications()->update(['is_read' => true]);
    }
}