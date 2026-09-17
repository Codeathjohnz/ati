<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    public const INCOME_CLASSES = [
        '1st Class', '2nd Class', '3rd Class', '4th Class', '5th Class', '6th Class',
    ];

    protected $fillable = [
        'region',
        'province',
        'city_municipality',
        'lgu_income_class',
    ];
}
