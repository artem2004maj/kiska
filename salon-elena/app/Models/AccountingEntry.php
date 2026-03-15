<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingEntry extends Model
{
    protected $fillable = [
        'date',
        'debit',
        'credit',
        'amount',
        'document_type',
        'document_id',
    ];
}