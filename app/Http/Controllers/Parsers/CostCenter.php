<?php

namespace App\Http\Controllers\Parsers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Builder as Builder;

class CostCenter extends Controller
{

    function __construct () {
        $this->builder = new Builder();
		$this->AST= [];
		
    }
    
    public function run ($fy, $id) {
		$this->parsedAST = [];
		$this->parsedAST[$fy] = new \StdClass();

        # get data
        $this->AST = $this->builder->run($fy, $id);


        # query HELL
        foreach($this->AST as $key => $val) {
			// empty next node
			$__AST_COPY = clone $val;
            $__AST_COPY->strategic_objectives = [];
            $__AST_COPY->total_peso = $__AST_COPY->total_dollar = 0;

			$this->parsedAST[$fy] = $__AST_COPY;
			
         	# strategic objective
			foreach($val->strategic_objectives as $obj_key => $obj_val) { 
				
				# operational objectives
				foreach($obj_val->operational_objectives as $opobj_key => $opobj_val) {
				
					# activities
					foreach($opobj_val->activities as $act_key => $act_val) {
			
						# budgetary requirements / line items
                        foreach($act_val->budgetary_requirements as $req_key => $req_val) {

                            # compute total amount
                            $this->parsedAST[$fy]->total_peso += $req_val->peso;
                            $this->parsedAST[$fy]->total_dollar += $req_val->dollar;
				
						}

					}

				}

			}

        }
        
		return $this->parsedAST;
    }
}
