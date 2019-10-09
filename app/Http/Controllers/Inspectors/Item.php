<?php

namespace App\Http\Controllers\Inspectors;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Parsers\Item as ItemParser;

class Item extends Controller
{
    function __construct () {
        $this->parser = new ItemParser();
    }

    public function show ($fy, $id, $itemId) {
        $__result = self::run($fy, $id, $itemId);
    }
    
    public function run ($fy, $id, $itemId) {
        $this->ast = $this->parser->run($fy, $id, $itemId);

        # build HTML files
        $__spacer = '&emsp;&emsp;';
        $__breaker = '<br/>';
        $__HTML = '<style>body { padding: 50px; font-size:12.5px; }</style>';
        $__HTML.= '<h3>AST Inspector</h3><p>Tree View for Plan Line Item per FY</p><hr/><br/><br/>';


          # query HELL
          foreach($this->ast->line_items as $key => $val) {  echo '<br/><br/>';
            $__HTML.="<details open><summary>{$__spacer}Line Item: {$key}</summary>{$__breaker}";

                $__HTML.="{$__breaker}<details open><summary>{$__spacer}{$__spacer} FY: {$val->fy->yeardesc}<b></b></summary>{$__breaker}";

                     # activities
                     foreach($val->fy->activities as $key_act => $val_act) {  
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
                            
                     }

                # end of activity
                $__HTML.="</details>";

            # end of FYP
            $__HTML.="</details>";
            
          }

          echo $__HTML;
        
    }
}
