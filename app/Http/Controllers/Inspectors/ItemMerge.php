<?php

namespace App\Http\Controllers\inspectors;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mergers\Item as ItemMerger;
use App\Http\Controllers\Account as Account;

class ItemMerge extends Controller
{
    function __construct () {
        $this->merger = new ItemMerger();
        $this->account = new Account();
        $this->department_name = '';
    }

    public function show ($fy, $id, $itemId) {
        $__result = self::run($fy, $id, $itemId);
    }

    private function get_user_details ($id) {
        return $this->account->view($id);
    }
    
    public function run ($fy, $id, $itemId) {
        $this->ast = $this->merger->merge($fy, $id, $itemId);
        $__account_info = (self::get_user_details($id));

        if(!isset($__account_info[0])) { 
            echo 'Invalid Account';
            exit;
        }

        # build HTML files
        $__spacer = '&emsp;&emsp;';
        $__breaker = '<br/>';
        $__HTML = '<style>body { padding: 50px; font-size:12.5px; }</style>';
        $__HTML.= '<h3>AST Inspector</h3><p>Tree View for Line Item per Cost Center</p><hr/><br/><br/>';

        # query HELL
        foreach($this->ast as $key => $val) {  echo '<br/><br/>';
            $__HTML.="<details open><summary>Cost Center{$__spacer}{$key}</summary>{$__breaker}";

            foreach($val as $line_key => $line_val) {
                $__HTML.="{$__breaker}<details open><summary>{$__spacer}{$__spacer} LINE ITEM: <b>{$line_key}</b></summary>{$__breaker}";
                foreach($line_val as $fy_key => $fy_val) {
                    $__HTML.="{$__breaker }{$__spacer}{$__spacer} 
                                {$__spacer}{$__spacer}<details open><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                                {$__spacer}<b> </b> <b>{$fy_key}</b></summary>{$__breaker}";
                    # activities
                    foreach($fy_val->activities as $key_act => $val_act) {  
                        $__HTML.="{$__breaker }{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer} 
                            {$__spacer}{$__spacer}<details open><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                            Activity: {$__spacer}<b> </b> <b>{$key_act}</b></summary>{$__breaker}";

                            foreach($val_act as $key_budg => $val_budg) {
                                $__HTML.="{$__breaker}{$__spacer}{$__spacer} 
                                {$__spacer}{$__spacer}<details open>
                                <summary>
                                    {$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                                    <u>Requirement #{$val_budg->lineid}</u>{$__spacer} <b>{$val_budg->lineitem}</b></summary>{$__breaker}
                                    {$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                                    {$__spacer}{$__spacer}<i>{$val_budg->remarks}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                                    <b>PHP: {$val_budg->peso}</b>
                                    {$__spacer}{$__spacer}
                                    <b>USD: {$val_budg->dollar}</b></i>";

                                # end of line items
                                $__HTML.="</details>";
                            }  
                    }

                    #end fy
                    $__HTML.="</details>";
                }

                $__HTML.="</details>";
            }

            # end of FYP
            $__HTML.="</details>";
        }
        
        echo $__HTML;

    }

   
}
