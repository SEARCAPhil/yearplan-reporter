<?php

namespace App\Http\Controllers\Reports;

// include autoloader
require '../vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mergers\CostCenter as CostCenterMerger;
use App\Http\Controllers\LineItem as LineItem;
use App\Http\Controllers\Yearplan as YearPlan;
use App\Http\Controllers\Account as Account;
use Dompdf\Dompdf;


class CostCenter extends Controller
{   
    
    public function __construct () {
        $this->merger = new CostCenterMerger();
        $this->line_item = new LineItem();
        $this->year_plan = new YearPlan();
        $this->account = new Account();
        $this->users = self::get_all_users();
        $this->style = self::get_style();
        $this->department_name = '';
        $this->date = date('M-d-Y');  
        $this->logo = 'img/logo.png';
        $this->lineCounter = $this->fyLineCounter = $this->activityLineCounter = 0;
        $this->line_items = self::get_line_items();
        $this->year_plan_names = [];
    }

    /**
     * 
     */
    private function get_all_users () {
        return $this->account->lists();
    }

    /**
     * 
     */
    private function get_line_items () {
        $__line_items = [];
        foreach($this->line_item->select_all() as $key => $val) {
            $__line_items[$val->line2desc] = $val->code;
        }
        return $__line_items;
    }

    /**
     * 
     */
    private function get_user_details ($id) {
        return $this->account->view($id);
    }

    /**
     * 
     */
    private function get_year_plan ($id) {
        $__year_plans = ($this->year_plan->get_yearplan_per_fyp ($id));
        $__year_plans_ids = [];
        $this->year_plan_names = [];

        foreach($__year_plans as $key => $val) {
            array_push($this->year_plan_names, $val->yeardesc);
            if($val->yearplanid)  array_push($__year_plans_ids, $val->yearplanid);
        }
        $this->fys = implode(' / ', $this->year_plan_names);
        return implode(',', $__year_plans_ids);
    }

    /**
     * 
     */
    private function get_style () {
        return '<style>
            @page { margin: 0 0;}
            body {
                font-size: 12px;
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
                width: 95%;
                font-size: 9.5px;
            }

            .table th.bordered {
                
            }

            .table th {
                border: 1px solid rgb(60,60,60);
                
            }

            .table tr.header {
                background: rgb(200,200,200);
                margin-top: 15px;
            }

            .table tr.header-light {
                background: rgb(250,250,250);
                margin-top: 15px;
            }
            

            .table tr td {
                padding: 5px;
                border: 1px solid rgb(60,60,60);
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

    /**
     * 
     */
    public function get_fy_header_table () {
        $table = '';
        # Fiscal Year Headers
        foreach($this->year_plan_names as $key => $val) {
            $table.="<th width='10px' class='bordered text-center' colspan='2'>{$val}</th>";
        }

        for($x = 0; $x < (count($this->year_plan_names)-5); $x++) {
            $table.="<th width='10px' class='bordered text-center' colspan='2'>&nbsp;</th>";
        }

        return $table;
    }

    /**
     * 
     */
    public function get_fy_header_amount () {
        $table = '';
        # Fiscal Year Headers
        foreach($this->year_plan_names as $key => $val) {
            $table.="<th width='5px' class='bordered text-center'>PESO</th>
            <th width='5px' class='bordered text-center'>DOLLAR</th>";
        }

        for($x = 0; $x < (count($this->year_plan_names)-5); $x++) {
            $table.="<th width='5px' class='bordered text-center'>&nbsp;</th>
            <th width='5px' class='bordered text-center'>&nbsp;</th>";
        }

        return $table;
    }


    /**
     * 
     */
    public function get_fy_td_amount ($peso = '', $dollar = '') {

        return "<td width='5px' class='bordered text-center'>{$peso}</td>
                <td width='5px' class='bordered text-center'>{$dollar}</td>";
    }

    public function get_cost_center_data ($id, $uid) {
        $__fyps = self::get_year_plan($id);
        
        # user info
        $__account_info = (self::get_user_details($uid));
        if(!isset($__account_info[0])) exit;
        if(empty($__fyps)) exit;
        $this->department_name = $__account_info[0]->fullname;

        # merge
        $this->ast = $this->merger->merge($__fyps, $uid);

        foreach($this->ast as $key => $value) {
            $table = ''; 
            $table.="<tr>
                        <td>{$key}</td>";
           # $table.= self::get_fy_td_amount();
           $__total_peso = $__total_dollar =0;
           foreach($this->year_plan_names as $key => $val) {
               # amount
                $__peso = @$value[$val]->total_peso;
                $__dollar = @$value[$val]->total_dollar; 
                $__total_peso += $__peso;
                $__total_dollar += $__dollar;
                $table.="<th width='5px' class='bordered text-center'>{$__peso}</th>
                <th width='5px' class='bordered text-center'>{$__dollar}</th>";
            }

            for($x = 0; $x < (count($this->year_plan_names)-5); $x++) {
                $table.="<th width='5px' class='bordered text-center'>&nbsp;</th>
                <th width='5px' class='bordered text-center'>&nbsp;</th>";
            }

            $table.="<th width='5px' class='bordered text-center'>{$__total_peso}</th>
                <th width='5px' class='bordered text-center'>{$__total_dollar}</th>";

            $table.="</tr>";   
        }

        return $table;
    }


    /**
     * 
     */
    public function get_html ($id, $uid) {
        
        $__fyps = self::get_year_plan($id);

        # Main table header
        $table = "<table class='table'>";
        $table.="
            <thead>
                <tr class='header'>
                    <th width='80px' class='bordered text-center' rowspan='2'>COST CENTERS</th>
                ";

        # get fiscal year headers        
        $table.= self::get_fy_header_table();
                
        $table.="<th width='40px' class='bordered text-center'  colspan='2'>TOTAL</th>
        <th width='40px' class='bordered text-center'  colspan='2'>CONSOLIDATED TOTAL</th>";

        $table.="</tr>
                <tr class='header'>";

        $table.= self::get_fy_header_amount();

        # peso dollar for total and consolidated header
        $table.="<th width='5px' class='bordered text-center'>DOLLAR</th>
                <th width='5px' class='bordered text-center'>PESO</th>
                <th width='5px' class='bordered text-center'>PESO</th>
                <th width='5px' class='bordered text-center'>DOLLAR</th>
                </tr>
                </thead><tbody>";
        
        foreach($this->users as $key => $value) {
            $table.=self::get_cost_center_data($id, $value->userid);
        }


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
                    <section style='width:100%;height:20px;font-size:smaller;'><br/><br/>
                        <b>FYDP Total Budgetary Requirements Per Cost Center</b><br/>
                        <small>
                            FY {$this->fys}<br/>
                            <b>(Peso & Dollar)</b>
                        </small>
                    </section>     
                </article>
            </header>

            
            <footer>
                <div style='width: 30%;'>Operational Planning Report - Date: {$this->date} </div>
                <div class='text-right'>Page <span class='page-number'> </span></div>
            </footer>

            <main>{$table}</main>

            </body>
            </html>";
            
        return $html;
    }

    public function print($fy, $uid) {
        // instantiate and use the dompdf class
        $dompdf = new Dompdf(array('enable_remote' => true));
        // options
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('defaultFont', 'Arial');

        $dompdf->loadHtml(self::get_html($fy, $uid));

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        #$fontMetrics->get_font("helvetica", "bold");
        #$pdf->page_text(72, 18, "Header: {PAGE_NUM} of {PAGE_COUNT}", $font, 6, array(0,0,0));
  

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream("dompdf_out.pdf", array("Attachment" => false));
    }
}
