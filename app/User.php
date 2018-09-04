<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'usertb';
    protected $fillable = ['adminrights', 'fullname', 'alias', 'username', 'password'];
    public $timestamps = false;

    public function objectives () {
        return $this->hasMany(Objective::class, 'userid');
    }
}
