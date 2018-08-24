<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Budget as BudgetResource;

class Budget extends Controller
{
  public function show ($fy, $id) {
    $__arr = array('id' => 1);
    return new BudgetResource($__arr);
  }

}
