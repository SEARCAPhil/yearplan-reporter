<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BudgetaryRequirement extends Model
{
    protected $table = "linetb";
    protected $fillable = ['activityid', 'line2id', 'lineitem', 'peso', 'dollar'];
    public $timestamps = false;
}
