<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'min_stock',
        'current_balance',
    ];

    public function materialReceipts()
    {
        return $this->hasMany(MaterialReceipt::class);
    }

    public function consumptions()
    {
        return $this->hasMany(Consumption::class);
    }
}