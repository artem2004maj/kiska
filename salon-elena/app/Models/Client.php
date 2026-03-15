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

    
}