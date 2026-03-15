<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumption extends Model
{
    protected $fillable = [
        'batch_number',
        'quantity',
        'cost_price',
        'provided_id',
        'material_id',
    ];

    public function providedService()
    {
        return $this->belongsTo(ProvidedService::class, 'provided_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}