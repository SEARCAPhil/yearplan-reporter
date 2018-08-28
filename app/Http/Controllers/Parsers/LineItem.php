<?php

namespace App\Http\Controllers\Parsers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Builder as Builder;

class LineItem extends Controller
{

    function __construct () {
        $this->builder = new Builder();
		$this->AST= [];
		$this->parsedAST = [];
    }
    
    public function run ($fy, $id) {
		$this->parsedAST[$fy] = new \StdClass();

        # get data
        $this->AST = $this->builder->run($fy, $id);

        # query HELL
        foreach($this->AST as $key => $val) {
			
		// empty next node
		$__AST_COPY = clone $val;
		$__AST_COPY->strategic_objectives = [];
		$this->parsedAST[$fy] = $__AST_COPY;
		// empty line items
		if(!isset($this->parsedAST[$fy]->line_items)) $this->parsedAST[$fy]->line_items = [];

         # strategic objective
          foreach($val->strategic_objectives as $obj_key => $obj_val) { 
            
            # operational objectives
            foreach($obj_val->operational_objectives as $opobj_key => $opobj_val) {
            
              # activities
              foreach($opobj_val->activities as $act_key => $act_val) {
               
                # budgetary requirements / line items
                foreach($act_val->budgetary_requirements as $req_key => $req_val) { 
					
					//activities
					$req_val->activities = [];
					if(!isset($req_val->activities[$act_val->activitydesc])) $req_val->activities[$act_val->activitydesc] = [];
					$req_val->activities[$act_val->activitydesc][] = $act_val;

					// line item
					if(!isset($this->parsedAST[$fy]->line_items[$req_val->line2desc])) $this->parsedAST[$fy]->line_items[$req_val->line2desc] = [];
					$this->parsedAST[$fy]->line_items[$req_val->line2desc][] = $req_val;

		
                }

              }

            }

          }

		}
		
		return $this->parsedAST;
    }
}
