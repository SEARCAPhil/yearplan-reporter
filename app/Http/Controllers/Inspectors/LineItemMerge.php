<?php

namespace App\Http\Controllers\inspectors;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mergers\LineItem as LineItemMerger;
use App\Http\Controllers\Account as Account;

class LineItemMerge extends Controller
{
    function __construct () {
        $this->merger = new LineItemMerger();
        $this->account = new Account();
        $this->department_name = '';
    }

    public function show ($fy, $id) {
        $__result = self::run($fy, $id);
    }

    private function get_user_details ($id) {
        return $this->account->view($id);
    }
    
    public function run ($fy, $id) {
        $this->ast = $this->merger->merge($fy, $id);
        $__account_info = (self::get_user_details($id));

        if(!isset($__account_info[0])) { 
            echo 'Invalid Account';
            exit;
        }
        
        $this->department_name = $__account_info[0]->fullname;

         # build HTML files
         $__spacer = '&emsp;&emsp;';
         $__breaker = '<br/>';
         $__HTML = '<style>body { padding: 50px; font-size:12.5px; }</style>';
         $__HTML.= "<h3>AST Inspector</h3><p>Tree View for Plan Line Item per FY (MERGED) - <b>{$this->department_name}</b></p><hr/><br/><br/>";

        foreach($this->ast as $key => $val) {  
            $__HTML.="<details open><summary>{$__spacer}Line Item: <b>{$key}</b></summary>{$__breaker}";
           

            foreach($val as $fy_key => $fy_val) {
                $__HTML.="<details open><summary>{$__spacer}{$__spacer}
                    FY: <b>{$fy_key}</b>
                    </summary>{$__breaker}";
                    $__HTML.="<p>{$__spacer}{$__spacer}{$__spacer}{$__spacer} <u>DETAILS</u></p>";
                    foreach($fy_val->activities as $f_key => $f_val) {
                        foreach($f_val as $act_key => $act_val) {
                            $__HTML.="<details open><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                            Activity: {$__spacer}<b>{$act_key}</b>
                            </summary>{$__breaker}{$__breaker}";
                            foreach($act_val as $budg_key => $budg_val) {
                                $__HTML.="<details open><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                                    Requirement # {$budg_val->lineid}: {$__spacer}<b>{$budg_val->lineitem}</b>
                                    </summary>{$__breaker}
                                    {$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                                    <i>{$budg_val->remarks}</i>{$__breaker}{$__breaker}";

                                    $__HTML.="</details>";
                            }

                            $__HTML.="</details>";
                        }
                    }
                    /*foreach($fy_val as $f_key => $f_val) {
                        $__HTML.="<details open><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                            Activity: {$__spacer}<b>{$f_key}</b>
                            </summary>{$__breaker}{$__breaker}";
                            
                            for($x = 0; $x < count($f_val); $x++) {
                                
                                foreach($f_val[$x] as $c => $d) {
                                    $__HTML.="<details open><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                                    Requirement # {$d->lineid}: {$__spacer}<b>{$d->lineitem}</b>
                                    </summary>{$__breaker}
                                    {$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                                    <i>{$d->remarks}</i>{$__breaker}{$__breaker}";

                                    $__HTML.="</details>";
                                }
                            }

                        $__HTML.="{$__breaker}</details>";
                    }
                    /*foreach($fy_val as $ac_key => $ac_val) {
                        foreach($ac_val as $act_key => $act_val) {   
                            $__HTML.="<details open><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                            Activity: <b>{$act_key}</b>
                            </summary>{$__breaker}";

                            

                            foreach($ac_val as $a_key => $a_val) {
                                var_dump($a_val);
                            }
                            $__HTML.="</details>";
                         
                        }
                    }*/

                $__HTML.="</details>";
            }
            
            /*foreach($val as $fy_key => $fy_val) { 
                $__HTML.="<details open><summary>{$__spacer}{$__spacer}
                    FY: <b>{$fy_key}</b>
                    </summary>{$__breaker}";

                    foreach($fy_val->activities as $act_key => $act_val) {
                        $__HTML.="<details open><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                        Activity: <b>{$act_key}</b>
                        </summary>{$__breaker}";

                        # budgetary requirements
                        for($x = 0; $x < count($act_val->budgetary_requirements); $x++) {
                            $__HTML.="<details open><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                             <b>{$act_val->budgetary_requirements[$x]->lineitem}</b>
                            </summary>{$__breaker}";

                                $__HTML.="{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                                <i>{$act_val->budgetary_requirements[$x]->remarks}</i>{$__breaker}{$__breaker}";

                            $__HTML.="</details>";
                        }
                        

                        $__HTML.="</details>";
                    }

                $__HTML.="</details>";
            }*/

            $__HTML.="</details>";
        }

        echo $__HTML;
    }
}
