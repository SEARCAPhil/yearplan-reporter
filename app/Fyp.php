<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fyp extends Model
{
    protected $table = 'fyp';
    protected $fillable = ['fyp_desc'];
    public $timestamps = false;

    public function year_plans () {
        return $this->hasMany(YearPlan::class, 'fyp_id');
    }
}
