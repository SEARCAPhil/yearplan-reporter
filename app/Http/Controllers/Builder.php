<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Budget as BudgetResource;
use App\Http\Controllers\Objective as Objective;
use App\Http\Controllers\OperationalObjective as OperationalObjective;
use App\Http\Controllers\BudgetaryRequirement as BudgetaryRequirement;
use App\Http\Controllers\Activity as Activity;
use App\Http\Controllers\Yearplan as Yearplan;

class Builder extends Controller
{

  function __construct () {
    $this->yearplanClass = new Yearplan();
    $this->objectiveClass = new Objective();
    $this->operationalObjectiveClass = new OperationalObjective();
    $this->budgetaryRequirementClass = new BudgetaryRequirement();
    $this->activityClass = new Activity();
    $this->ast = [];
  }

  public function show ($fy, $id) {
    $__result = self::run($fy, $id);
    return new BudgetResource($__result);
  }

  public function run ($fy, $id) {
    $this->ast = [];
    # year plan
    foreach($this->yearplanClass->view($fy) as $key => $val) {
      # strategic objectives
      $__arr = $this->objectiveClass->get_objectives_per_fy_and_user($fy, $id);
      $val->strategic_objectives = $__arr;

      foreach($val->strategic_objectives as $key_strat => $val_strat) {
        # get operational objectives
        $__operational_objectives = $this->operationalObjectiveClass->get_operational_objectives_per_objective($val_strat->objectid);
        $val_strat->operational_objectives = $__operational_objectives;

        # get activities
        foreach($val_strat->operational_objectives as $key => $opt_val) {
          $__activities = $this->activityClass->get_activities_per_operational_objective($opt_val->opid);
          $opt_val->activities = $__activities;

          # get budgetary requirement
          foreach($__activities as $key => $req_val) {
            $__requirements = $this->budgetaryRequirementClass->get_budgetary_requirements_per_activity($req_val->activityid);
            $req_val->budgetary_requirements = $__requirements;
          }
        }
      }
      $this->ast[] = $val;
    }

    return $this->ast;
  }
}
