<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    protected $table = 'line2tb';
    protected $fillable = ['code', 'line2desc'];
    public $timestamps = false;
}
