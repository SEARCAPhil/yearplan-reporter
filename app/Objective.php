<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Objective extends Model
{
    protected $table = 'objectivetb';
    protected $fillable = ['fyid', 'objectives'];
    public $timestamps = false;

    public function operational_objectives () {
        return $this->hasMany(OperationalObjective::class, 'objectid');
    }
}
