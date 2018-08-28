<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationalObjective extends Controller
{
    public function get_operational_objectives_per_objective ($id) {
        return DB::select('SELECT * FROM opobjectivetb WHERE objectid = ?', [$id]);  
    }

    public function view ($id) {
        return DB::select('SELECT * FROM opobjectivetb WHERE opid = ?', [$id]);  
    }
}
