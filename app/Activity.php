<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activitytb';
    protected $fillable = ['activitydesc'];
    public $timestamps = false;

    public function budgetary_requirements () {
        return $this->hasMany(BudgetaryRequirement::class, 'activityid');
    }
}
