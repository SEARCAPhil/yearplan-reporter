<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Account extends Controller
{
    public function view ($id) {
        return DB::select('SELECT * FROM usertb WHERE userid = ?', [$id]);  
    }
}
