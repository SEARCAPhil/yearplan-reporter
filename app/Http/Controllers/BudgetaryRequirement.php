<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetaryRequirement extends Controller
{
    public function get_budgetary_requirements_per_activity ($id) {
        return DB::select('SELECT * from linetb LEFT JOIN line2tb ON linetb.line2id = line2tb.line2id WHERE activityid = ?', [$id]);  
    }

    public function view ($id) {
        return DB::select('SELECT * from linetb LEFT JOIN line2tb ON linetb.line2id = line2tb.line2id WHERE lineid = ?', [$id]);  
    }
}
