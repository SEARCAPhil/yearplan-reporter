<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Budget as BudgetResource;
use App\Http\Controllers\Builder as Builder;


class InspectorFiscalYear extends Controller
{

  function __construct () {
    $this->builder = new Builder();
  }

  public function inspect ($fy, $id) {
    # get data
    $this->ast = $this->builder->run($fy, $id);

    # build HTML files
    $__spacer = '&emsp;&emsp;';
    $__breaker = '<br/>';
    $__HTML = '<style>body { padding: 50px; font-size:12.5px; }</style>';
    $__HTML.= '<h3>AST Inspector</h3><p>Tree View for Fiscal Year</p><hr/><br/><br/>';

    # query HELL
    foreach($this->ast as $key => $val) {
      $__HTML.="<details open><summary>{$__spacer}YEAR PLAN: #{$val->yearplanid} {$val->yeardesc}</summary>{$__breaker}";

      # objective data
      foreach($val->strategic_objectives as $obj_key => $obj_val) { 
         # objectives
        $__HTML.="{$__breaker }{$__spacer}{$__spacer} 
        {$__spacer}{$__spacer}<details open><summary>{$__spacer}{$__spacer}
        <u>STRATEGIC OBJECTIVE #{$obj_val->objectid}</u>{$__spacer}<b>{$obj_val->objectives}</b></summary>{$__breaker}";

      
        foreach($obj_val->operational_objectives as $opobj_key => $opobj_val) {
          # operational objectives
          $__HTML.="{$__breaker }{$__spacer}{$__spacer}
          <details open><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}<u>OBJECTIVE # {$opobj_val->opid}</u> {$__spacer}<b>{$opobj_val->operationalobjective}</b></summary>
          ";
          
          # activities
          foreach($opobj_val->activities as $act_key => $act_val) {
            $__HTML.="{$__breaker }{$__spacer}{$__spacer}
            <details open><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
            <u>Activity # {$act_val->activityid}</u> {$__spacer}<b>{$act_val->activitydesc}</b></summary>
            ";

            # budgetary requirements / line items
            foreach($act_val->budgetary_requirements as $req_key => $req_val) {
              $__HTML.="{$__breaker}{$__spacer}{$__spacer}
              <details><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
              <u>Line Item # {$req_val->lineid}</u>{$__spacer}{$__spacer}{$req_val->code}{$__spacer} <b>{$req_val->lineitem}</b>
              {$__spacer}{$__spacer}{$__spacer} PHP: {$req_val->peso}
              {$__spacer}{$__spacer}{$__spacer} USD: {$req_val->dollar}
              
              {$__breaker}</summary>
              {$__breaker}
              {$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
              {$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}&nbsp;
              <em>{$req_val->line2desc}</em>
              ";

              # end of requirements
              $__HTML.="</details>";
            }

            # end of activities
            $__HTML.="</details>";
          }
          
          # end of operational objectives
          $__HTML.="</details>";
        }
        
      }

      # end of objectives
      $__HTML.="</details>";

    # end of FYP
    $__HTML.="</details>";

    }

    echo $__HTML;
  }

}
