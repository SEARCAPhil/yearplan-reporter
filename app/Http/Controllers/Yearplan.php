<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Yearplan extends Controller
{
    public function get_yearplan_per_fyp ($id) {
        return DB::select('SELECT * FROM yearplantb WHERE fyp_id = ?', [$id]);  
    }

    public function view ($id) {
        return DB::select('SELECT * FROM yearplantb WHERE yearplanid = ?', [$id]);  
    }
}
