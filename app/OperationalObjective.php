<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OperationalObjective extends Model
{
    protected $table = 'opobjectivetb';
    protected $fillable = ['operationalobjective'];
    public $timestamps = false;

    public function activities () {
        return $this->hasMany(Activity::class, 'opid');
    }
}
