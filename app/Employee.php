<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    // use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'department','base_salary','monthly_bonus'
    ];
}
