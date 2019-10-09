<?php

namespace App\Http\Controllers\Parsers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Builder as Builder;

class Item extends Controller
{

    function __construct () {
      $this->builder = new Builder();
		  $this->AST= [];
		
    }
    
    public function run ($fy, $id, $itemId) {
		  $this->parsedAST = [];
      $this->parsedAST[$fy] = new \StdClass();
      $this->fyDetails = new \StdClass();

      # get data
      $this->AST = $this->builder->run($fy, $id);

      # query HELL
      foreach($this->AST as $key => $val) {
			
        // empty next node
        $__AST_COPY = clone $val;
        $__AST_COPY->strategic_objectives = [];

        $this->parsedAST = $__AST_COPY;
        
			  // empty line items
        if(!isset($this->parsedAST->line_items)) $this->parsedAST->line_items = [];

        # copy fy details
        $this->fyDetails = clone $this->parsedAST;

         	# strategic objective
			  foreach($val->strategic_objectives as $obj_key => $obj_val) { 
				
				# operational objectives
				foreach($obj_val->operational_objectives as $opobj_key => $opobj_val) {
				
					# activities
					foreach($opobj_val->activities as $act_key => $act_val) {
			
						# budgetary requirements / line items
						foreach($act_val->budgetary_requirements as $req_key => $req_val) { 
              
              # NOTE
              # This will ensure that the result will only contain specific line item
              if($req_val->line2id == $itemId) {
                // line item
                if(!isset($this->parsedAST->line_items[$req_val->line2desc])) $this->parsedAST->line_items[$req_val->line2desc] = new \StdClass();
                if(!isset($this->parsedAST->line_items[$req_val->line2desc]->fy)) $this->parsedAST->line_items[$req_val->line2desc]->fy = clone $this->fyDetails;
                if(!isset($this->parsedAST->line_items[$req_val->line2desc]->fy->activities)) $this->parsedAST->line_items[$req_val->line2desc]->fy->activities = [];
                if(!isset($this->parsedAST->line_items[$req_val->line2desc]->fy->activities[$act_val->activitydesc])) $this->parsedAST->line_items[$req_val->line2desc]->fy->activities[$act_val->activitydesc] = [];
                $this->parsedAST->line_items[$req_val->line2desc]->fy->activities[$act_val->activitydesc][] = $req_val;
              }
				
						}

					}

				}

			}

		}
		
		return $this->parsedAST;
    }
}
