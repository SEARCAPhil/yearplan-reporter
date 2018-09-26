<?php

namespace App\Http\Controllers\Inspectors;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Parsers\CostCenter as CostCenterParser;
use App\Http\Controllers\Account as Account;

class CostCenter extends Controller
{
    function __construct () {
        $this->parser = new CostCenterParser();
        $this->account = new Account();
        $this->department_name = '';
    }

    private function get_user_details ($id) {
        return $this->account->view($id);
    }

    public function show ($fy, $id) {
        $__result = self::run($fy, $id);
    }
    
    public function run ($fy, $id) {
        $this->ast = $this->parser->run($fy, $id);

        # account information
        $__account_info = (self::get_user_details($id));
        if(!isset($__account_info[0])) { 
            echo 'Invalid Account';
            exit;
        }
        # department
        $this->department_name = $__account_info[0]->fullname;

        # build HTML files
        $__spacer = '&emsp;&emsp;';
        $__breaker = '<br/>';
        $__HTML = '<style>body { padding: 50px; font-size:12.5px; }</style>';
        $__HTML.= "<h3>AST Inspector</h3><p>Tree View for FY total amount per cost center ({$this->department_name})</p><hr/><br/><br/>";
     
         # query HELL
        foreach($this->ast as $key => $val) {  echo '<br/><br/>';
            $__HTML.="<details open><summary>{$__spacer}YEAR PLAN: #{$val->yearplanid} {$val->yeardesc}</summary>{$__breaker}";
            
            # amount
            $__HTML.="<p>{$__spacer}{$__spacer}Peso : {$val->total_peso}</p>";
            $__HTML.="<p>{$__spacer}{$__spacer}Dollar : {$val->total_dollar}</p>";

            # end of FYP
            $__HTML.="</details>";
        }
        echo $__HTML;
        
    }
}
