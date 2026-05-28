<?php
// app/Models/CompanySetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    protected $table = 'company_settings';
    
    protected $fillable = [
        'company_name',
        'short_name',
        'logo_path',
        'favicon_path',
        'inn',
        'kpp',
        'ogrn',
        'okpo',
        'legal_address',
        'actual_address',
        'phone',
        'email',
        'website',
        'director_name',
        'accountant_name',
        'bank_details',
        'document_header',
        'document_footer',
        'stamp_path',
        'instagram',
        'telegram',
        'vk',
        'additional',
    ];
    
    protected $casts = [
        'bank_details' => 'array',
        'additional' => 'array',
    ];
    
    /**
     * Получить значение настройки
     */
    public static function get($key, $default = null)
    {
        $settings = self::getCached();
        return $settings->$key ?? $default;
    }
    
    /**
     * Получить все настройки с кешированием
     */
    public static function getCached()
    {
        return Cache::remember('company_settings', 3600, function () {
            return self::first() ?? new self();
        });
    }
    
    /**
     * Очистить кеш настроек
     */
    public static function clearCache()
    {
        Cache::forget('company_settings');
    }
    
    /**
     * Получить URL логотипа
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return Storage::url($this->logo_path);
        }
        return null;
    }
    
    /**
     * Получить URL печати
     */
    public function getStampUrlAttribute()
    {
        if ($this->stamp_path && Storage::disk('public')->exists($this->stamp_path)) {
            return Storage::url($this->stamp_path);
        }
        return null;
    }
    
    /**
     * Получить полное название для документов
     */
    public function getFullNameForDocumentAttribute()
    {
        $name = $this->company_name;
        if ($this->inn) {
            $name .= " (ИНН: {$this->inn})";
        }
        return $name;
    }
    
    /**
     * События модели
     */
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('company_settings_frontend');
        });
        
        static::deleted(function () {
            Cache::forget('company_settings_frontend');
        });
    }
}