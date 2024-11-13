<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'tax_id',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'logo',
        'website',
    ];
}
