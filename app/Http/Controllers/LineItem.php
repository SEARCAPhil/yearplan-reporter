<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LineItem extends Controller
{
    public function select_all () {
        return DB::select('SELECT * from line2tb');  
    }
}
