<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Fyp extends Controller
{

    public function view ($id) {
        return DB::select('SELECT * FROM fyp_tb WHERE fyp_id = ?', [$id]);  
    }
}
