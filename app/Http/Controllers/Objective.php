<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Objective extends Controller
{
    public function get_objectives_per_fy ($id) {
        return DB::select('SELECT * FROM objectivetb WHERE fyid = ?', [$id]);  
    }

    public function get_objectives_per_fy_and_user ($id, $userid) {
        return DB::select('SELECT * FROM objectivetb WHERE fyid = ? and userid = ?', [$id, $userid]);  
    }

    public function view ($id) {
        return DB::select('SELECT * FROM objectivetb WHERE objectid = ?', [$id]);  
    }
}
