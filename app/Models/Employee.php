<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasApiTokens, HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'salary' => 'decimal:2',
            'hiring_date' => 'date',
            'password' => 'hashed',
        ];
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }
}
