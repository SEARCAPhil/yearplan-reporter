<?php

namespace App\Http\Controllers\Inspectors;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Parsers\LineItem as LineItemParser;

class LineItem extends Controller
{
    function __construct () {
        $this->parser = new LineItemParser();
    }

    public function show ($fy, $id) {
        $__result = self::run($fy, $id);
    }
    
    public function run ($fy, $id) {
        $this->ast = $this->parser->run($fy, $id);

     
        # build HTML files
        $__spacer = '&emsp;&emsp;';
        $__breaker = '<br/>';
        $__HTML = '<style>body { padding: 50px; font-size:12.5px; }</style>';
        $__HTML.= '<h3>AST Inspector</h3><p>Tree View for Plan Line Item per FY</p><hr/><br/><br/>';

         # query HELL
        foreach($this->ast as $key => $val) {  echo '<br/><br/>';
            $__HTML.="<details open><summary>{$__spacer}YEAR PLAN: #{$val->yearplanid} {$val->yeardesc}</summary>{$__breaker}";

            # line items
            foreach($val->line_items as $line_key => $line_val) { 
                $__HTML.="{$__breaker}<details open><summary>{$__spacer}{$__spacer} LINE ITEM: <b>{$line_key}</b></summary>{$__breaker}";
                    foreach($val->line_items[$line_key] as $l_key => $l_val) {
                        
                        # activities
                        foreach($l_val as $key_act => $val_act) {  
                            $__HTML.="{$__breaker }{$__spacer}{$__spacer} 
                                {$__spacer}{$__spacer}<details open><summary>{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                                Activity: {$__spacer}<b> </b> <b>{$key_act}</b></summary>{$__breaker}";

                                foreach($val_act as $key_budg => $val_budg) {
                                    $__HTML.="{$__breaker}{$__spacer}{$__spacer} 
                                    {$__spacer}{$__spacer}<details open>
                                    <summary>
                                        {$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                                        <u>Requirement #{$val_budg->lineid}</u>{$__spacer} <b>{$val_budg->lineitem}</b></summary>{$__breaker}
                                        {$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}{$__spacer}
                                        {$__spacer}{$__spacer}<i>{$val_budg->remarks}{$__spacer}{$__spacer}
                                        <b>PHP: {$val_budg->peso}</b>
                                        {$__spacer}{$__spacer}
                                        <b>USD: {$val_budg->dollar}</b></i>";

                                    # end of line items
                                    $__HTML.="</details>";
                                }  

                            $__HTML.="</details>";
                           
                        }

                    } 
                 # end of line items
                 $__HTML.="</details>";
            }

            # end of FYP
            $__HTML.="</details>";
        }
        echo $__HTML;
        
    }
}
