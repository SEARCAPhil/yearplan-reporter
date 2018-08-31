<?php

namespace App\Http\Controllers\Reports;

// include autoloader
require '../vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mergers\LineItem as LineItemMerger;
use App\Http\Controllers\LineItem as LineItem;
use App\Http\Controllers\Yearplan as YearPlan;
use Dompdf\Dompdf;


class LineItemMerge extends Controller
{   
    
    public function __construct () {
        $this->merger = new LineItemMerger();
        $this->line_item = new LineItem();
        $this->year_plan = new YearPlan();
        $this->date = date('M-d-Y');  
        $this->logo = public_path('img/logo.png');
        $this->style = self:: get_style();
        $this->lineCounter = $this->fyLineCounter = $this->activityLineCounter = 0;
        $this->line_items = self::get_line_items();
       
    }

    private function get_line_items () {
        $__line_items = [];
        foreach($this->line_item->select_all() as $key => $val) {
            $__line_items[$val->line2desc] = $val->code;
        }

        return $__line_items;
    }

    private function get_year_plan ($id) {
        $__year_plans = ($this->year_plan->get_yearplan_per_fyp ($id));
        $__year_plans_ids = [];

        foreach($__year_plans as $key => $val) {
            if($val->yearplanid)  array_push($__year_plans_ids, $val->yearplanid);
        }

        return implode(',', $__year_plans_ids);
    }

    private function get_activities ($data) {

        # increment linitem number from new instance
        # this will prevent overriding already declared line number
        $this->activityLineCounter = $this->fyLineCounter; 
        $__fy_html_buffer = '';
        $__result = new \StdClass();
        $__total_fy_peso = $__total_fy_dollar = 0;

        foreach($data as $act_key => $act_val) {

            $this->activityLineCounter++;
            $total_activity_peso = $total_activity_dollar = 0;
            
            # budgetary_ requirement buffer
            # this will allow us to compute the total amount of budgetary requirements per
            # activity before displaying the activity TR
            $budgetary_html_buffer = '';

            # increment budget line number number from new instance
            $this->requirementLineCounter =  $this->activityLineCounter;

            # BUDGETARY REQUIREMENTS
            $budgetary_req = self::get_budgetary_requirements($act_val);
            $budgetary_html_buffer.= $budgetary_req->html ;
            $total_activity_peso = $budgetary_req->peso; 
            $total_activity_dollar = $budgetary_req->dollar;
            
            
            # activity header
            # number notation
            $__total_activity_peso_eng = number_format($total_activity_peso, 2, '.', ',');
            $__total_activity_dollar_eng = number_format($total_activity_dollar, 2, '.', ',');

            $__fy_html_buffer.="<tr> 
                <td class='text-right  v-top'>{$this->activityLineCounter}</td>
                <td class='bold'> &nbsp; </td>
                <td class='text-left' style='padding-left:20px;word-wrap: break-word;'>
                    <span class='text-green bold text-left' style='float:left;height: auto; margin-bottom:10px;'>{$act_key}</span>
                </td>
                <td class='text-right bold'>&nbsp;</td>
                <td class='text-right bold text-green bold v-top'>{$__total_activity_peso_eng}</td>
                <td class='text-right bold'>&nbsp;</td>
                <td class='text-right bold'>&nbsp;</td>
                <td class='text-right bold text-green bold v-top'>{$__total_activity_dollar_eng}</td>
                <td class='text-right bold'>&nbsp;</td>
            </tr>";
            
            # concatenate budget buffer under this activity
            $__fy_html_buffer.= $budgetary_html_buffer ;

            # add total activity amount to FY
            $__total_fy_peso+= $total_activity_peso;
            $__total_fy_dollar+= $total_activity_dollar;

            # set the activity line number to the last count of budgetary requirement
            # this will prevent repeating activity number
            $this->activityLineCounter = $this->requirementLineCounter;
        }

        # return results value
        $__result->html =  $__fy_html_buffer;
        $__result->peso = $__total_fy_peso;
        $__result->dollar = $__total_fy_dollar;
        return $__result;
    }

    private function get_budgetary_requirements ($data) {
        $__budgetary_html_buffer = '';
        $__total_activity_peso = $__total_activity_dollar = 0;
        $__result = new \StdClass();

        foreach($data as $budg_key => $budg_val) {
            # next line 
            $this->requirementLineCounter++;

            # compute total amount per activity
            $__total_activity_peso+= $budg_val->peso;
            $__total_activity_dollar+= $budg_val->dollar;

            # number notation
            $__budg_val_peso_eng = number_format($budg_val->peso, 2, '.', ',');
            $__budg_val_dollar_eng = number_format($budg_val->dollar, 2, '.', ',');

            # html
            $__budgetary_html_buffer.="<tr> 
                <td class='text-right v-top'>{$this->requirementLineCounter}</td>
                <td class='bold'> &nbsp; </td>
                <td class='text-left' style='padding-left:40px;float: left;'><br/>
                    <span class='bold'>{$budg_val->lineitem}</span><br/>
                    <i>{$budg_val->remarks}</i>
                </td>
                <td class='text-right v-top'>{$__budg_val_peso_eng}</td>
                <td class='text-right bold'>&nbsp;</td>
                <td class='text-right bold'>&nbsp;</td>
                <td class='text-right v-top'>{$__budg_val_dollar_eng}</td>
                <td class='text-right bold'>&nbsp;</td>
                <td class='text-right bold'>&nbsp;</td>
            </tr>";
        }

        # return results value
        $__result->html =  $__budgetary_html_buffer;
        $__result->peso = $__total_activity_peso;
        $__result->dollar = $__total_activity_dollar;
        return $__result;
    }


    private function get_style () {
        return '<style>
            @page { margin: 0 0;}
            body {
                margin-top: 4cm;
                margin-left: 1cm;
                margin-right: 1cm;
                margin-bottom: 2cm;
            }

            header {
                position: fixed;
                top: 0cm;
                left: 0cm;
                right: 0cm;
                height: 4.3cm;
                padding: 40px;
                padding-top: 50px;
            }

            .table {
                border-spacing: 0px;
                border-collapse: separate;
                width: 100%;
                font-size: 9.5px;
            }

            .table th.bordered {
                border-bottom:1px solid #000;
            }

            .table tr.header {
                background: rgb(240,240,240);
                margin-top: 15px;
            }
            

            .table tr td {
                padding: 5px;
                border: none !important;
                height: auto;
            }

            .bold {
                font-weight: bold;
            }

            header p {
                font-size:15px;
            }

            footer { 
                position: fixed; 
                top: 95%;
                bottom: 0; 
                left: 0px; 
                right: 0px; 
                height: 5%; 
                font-size: 10px; 
                padding: 10px;
                padding-left: 5%;
                padding-right: 5%;
                box-sizing: border-box;
            }

            footer .page-number:before { 
                content: counter(page);
                
            }


            .first-page {
                margin: 0in;
                color: green;
                height: 100%;
                width: 100%;
                position:absolute;
                page-break-after: always;
            }

            main.page{
                margin-top:15%;
                width:100%;
                height:80%;
                page-break-before: always;
                background:red;
            }

            .text-center{
                text-align: center;
            }

            .text-right{
                text-align: right;
            }

            .text-red {
                color: red;
            }

            .text-green {
                color: green;
            }
            
            .v-top {
                vertical-align: top;
            }
        </style>';
    }

    public function get_html ($id) {
        $__fyps = self::get_year_plan($id);
        if(empty($__fyps)) exit;
        # merge
        $this->ast = $this->merger->merge($__fyps, 1);

        # total amount
        $grand_total_peso = $grand_total_dollar = 0;

        # Main table header
        $table = "<table class='table'>";
        $table.="
            <thead>
                <th width='15px' class='text-right'>&nbsp;</th>
                <th width='25px' class='bordered'>&nbsp;</th>

                <th width='300px' class='bordered'>&nbsp;</th>
                <th width='40px' class='text-right bordered'>&nbsp</th>

                <th width='40px' class='text-right bordered'>PESO</th>
                <th width='40px' class='text-right bordered'>&nbsp</th>
                <th width='50px' class='text-right bordered'>&nbsp</th>

                <th width='40px' class='text-right bordered'>DOLLAR</th>
                <th width='40px' class='text-right bordered'>&nbsp</th>
                <th width='50px' class='text-right bordered'>&nbsp</th>
            </thead>
            <tbody>";
            
            # LINE ITEM
            foreach($this->ast as $key => $val) { 
                $this->lineCounter++;  

                # line_item buffer
                # this will allow us to compute the overall amount of activities uder this line item
                # before displaying the Line item table header
                $line_item_html_buffer = ''; 

                # total amount
                $total_line_item_peso = $total_line_item_dollar = 0;

                # increment line item number from new instance
                # this will prevent overriding already declared line number
                $this->fyLineCounter = $this->lineCounter; 

                # FISCAL YEAR
                foreach($val as $fy_key => $fy_val) {
                    
                    $this->fyLineCounter++;

                    # fiscal_year buffer
                    # this will allow us to compute the total amount of activities per
                    # fiscal year before displaying the FY table header
                    $fy_html_buffer = ''; 

                    # total amount
                    $total_fy_peso = $total_fy_dollar = 0;

                    # ACTIVITIES
                    foreach($fy_val->activities as $f_key => $f_val) {
                        $activities = self::get_activities($f_val);
                        $fy_html_buffer.= $activities->html;
                        $total_fy_peso = $activities->peso;
                        $total_fy_dollar = $activities->dollar;
                    }

                    # Fiscal year header
                    # number notation
                    $total_fy_peso_eng = number_format($total_fy_peso, 2, '.', ',');
                    $total_fy_dollar_eng = number_format($total_fy_dollar, 2, '.', ',');

                    $line_item_html_buffer.="<tr> 
                        <td class='text-right v-top'>{$this->fyLineCounter}</td>
                        <td class='bold'> &nbsp; </td>
                        <td class='text-left bold  text-red'>&nbsp;FY {$fy_key} </td>
                        <td class='text-right bold'>&nbsp;</td>
                        <td class='text-right bold'>&nbsp;</td>
                        <td class='text-right bold text-red v-top'>{$total_fy_peso_eng}</td>
                        <td class='text-right bold'>&nbsp;</td>
                        <td class='text-right bold'>&nbsp;</td>
                        <td class='text-right bold text-red v-top'>{$total_fy_dollar_eng}</td>
                    </tr>";
                    # concatenate fy buffer under this line item
                    $line_item_html_buffer.=$fy_html_buffer;

                                            
                    # amount
                    $total_line_item_peso+=$total_fy_peso;
                    $total_line_item_dollar+=$total_fy_dollar; 
                    
                    $this->fyLineCounter = $this->activityLineCounter;

                    
                }

                # Line item header
                # number notation
                $total_line_item_peso_eng = number_format($total_line_item_peso, 2, '.', ',');
                $total_line_item_dollar_eng = number_format($total_line_item_dollar, 2, '.', ',');
                $table.="<tr class='header'> 
                    <td class='text-right'>{$this->lineCounter}</td>
                    <td>&nbsp;<b>{$this->line_items[$key]}</b></td>
                    <td class='bold'> {$key} </td>
                    <td class='text-right bold'>&nbsp;</td>
                    <td class='text-right bold'>&nbsp;</td>
                    <td class='text-right bold'>&nbsp;</td>
                    <td class='text-right bold'>{$total_line_item_peso_eng}</td>
                    <td class='text-right bold'>&nbsp;</td>
                    <td class='text-right bold'>&nbsp;</td>
                    <td class='text-right bold'>{$total_line_item_dollar_eng}</td>
                </tr>";
                
                # concatenate line item to  table
                $table.=$line_item_html_buffer;
                
                # amount
                $grand_total_peso+=$total_line_item_peso;
                $grand_total_dollar+=$total_line_item_dollar;

                $this->lineCounter = $this->fyLineCounter;
            }
        
            
        # grand total
        $grand_total_dollar_eng = number_format($grand_total_dollar, 2, '.', ',');
        $table.="<tr class='header'> 
                <td colspan='3' class='bold'>GRAND TOTAL</td>
                <td class='text-right bold'>&nbsp;</td>
                <td class='text-right bold'>&nbsp;</td>
                <td class='text-right bold'>&nbsp;</td>
                <td class='text-right bold'>{$grand_total_peso}</td>
                <td class='text-right bold'>&nbsp;</td>
                <td class='text-right bold'>&nbsp;</td>
                <td class='text-right bold'>{$grand_total_dollar_eng}</td>
            </tr>";

        # end of table
        $table.="</tbody></table>";

        $html = "<html>
            <head>
                <title>Details of Total Budget</title>
            {$this->style}</head>
            <body>
            <header>
                <img src='{$this->logo}' width='150px'/>
                <article>
                    <section style='width:100%;height:20px;text-align:center;font-size:smaller;'>
                        <b>KMD - Training Unit</b><br/>
                        <small>
                            Details of Total Budget<br/>
                        </small>
                    </section>     
                </article>
            </header>
            <footer style='display:block;'>
                <hr/>
                <div style='float: left;width: 30%;height:100%;'>Operational Planning Report - Date: {$this->date} </div>
                <div class='text-right'>Page <span class='page-number'> </span></div>
            </footer>
            <main>{$table}</main>

            </body>
            </html>";
            
        return $html;
    }

    public function print($fy, $id) {
        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        // options
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('defaultFont', 'Arial');

        $dompdf->loadHtml(self::get_html($fy));

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        #$fontMetrics->get_font("helvetica", "bold");
        #$pdf->page_text(72, 18, "Header: {PAGE_NUM} of {PAGE_COUNT}", $font, 6, array(0,0,0));
  

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream("dompdf_out.pdf", array("Attachment" => false));
    }
}
