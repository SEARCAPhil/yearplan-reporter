<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class YearPlan extends Model
{
    protected $table = 'yearplantb';
    protected $fillable = ['yeardesc', 'exchangerate'];
    public $timestamps = false;
}
