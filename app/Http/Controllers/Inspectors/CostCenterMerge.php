<?php

namespace App\Http\Controllers\inspectors;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mergers\CostCenter as CostCenterMerger;
use App\Http\Controllers\Account as Account;

class CostCenterMerge extends Controller
{
    function __construct () {
        $this->merger = new CostCenterMerger();
        $this->account = new Account();
    }
    

    public function show ($fy, $id) {
        $__result = self::run($fy, $id);
    }

    private function get_user_details ($id) {
        return $this->account->view($id);
    }
    
    public function run ($fy, $id) {
        $this->ast = $this->merger->merge($fy, $id);
        

        # build HTML files
        $__spacer = '&emsp;&emsp;';
        $__breaker = '<br/>';
        $__HTML = '<style>body { padding: 50px; font-size:12.5px; }</style>';
        $__HTML.= "<h3>AST Inspector</h3><p>Tree View for FY total amount per cost center</p><hr/><br/><br/>";
        
        # cost center name
        foreach($this->ast as $key => $value) {
            $__HTML.= "<details open><summary><u>{$key}</u></summary><br/>"; 

            # fiscal year
            foreach($value as $key_fy => $value_fy) {
                $__HTML.= "<details open><summary>{$__spacer}<b>{$key_fy}</b></summary>";
                    # amount
                    $__HTML.= "<p>{$__spacer}{$__spacer}Peso : {$value_fy->total_peso}</p>";
                    $__HTML.= "<p>{$__spacer}{$__spacer}Dollar : {$value_fy->total_dollar}</p>";

                $__HTML.= "</details>";
            }

            $__HTML.= "</details>"; 

        }

        $__HTML.="</details>";
        

        echo $__HTML;
    }
}
