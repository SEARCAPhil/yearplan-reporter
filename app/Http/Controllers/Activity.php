<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Activity extends Controller
{
    public function get_activities_per_operational_objective ($id) {
        return DB::select('SELECT * from activitytb WHERE opid = ?', [$id]);  
    }

    public function view ($id) {
        return DB::select('SELECT * from activitytb WHERE activityid = ?', [$id]);  
    }
}
