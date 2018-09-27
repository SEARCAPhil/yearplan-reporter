<?php

namespace App\Http\Controllers\Reports;

// include autoloader
require '../vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mergers\CostCenter as CostCenterMerger;
use App\Http\Controllers\LineItem as LineItem;
use App\Http\Controllers\Yearplan as YearPlan;
use App\Http\Controllers\Fyp as Fyp;
use App\Http\Controllers\Account as Account;
use Dompdf\Dompdf;


class CostCenter extends Controller
{   
    
    public function __construct () {
        $this->merger = new CostCenterMerger();
        $this->line_item = new LineItem();
        $this->year_plan = new YearPlan();
        $this->fyp = new Fyp();
        $this->account = new Account();
        $this->users = self::get_all_users();
        $this->style = self::get_style();
        $this->adminGroup = 1;
        $this->programGroup = 2;
        $this->filter_accounts = '';
        $this->department_name = '';
        $this->year_plan_names = [];
        $this->lineCounter = $this->fyLineCounter = $this->activityLineCounter = 0;
        $this->line_items = self::get_line_items();        
        $this->date = date('M-d-Y');  
        $this->logo = 'img/logo.png';
        $this->isMOOE = false;
        $this->exluded_line_items = ['user-centric maximized database'];    
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
        $this->fyp_details = $this->fyp->view($id);
        $__year_plans_ids = [];
        $this->year_plan_names = [];
        $this->year_plans =  $__year_plans;

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
                font-size: 10px;
                margin-top: 5cm;
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
                width: 91.5%;
            }

            hr {
                border:none;
                border-bottom: 1px solid #000;
                height: 1px;
            }

            .table {
                border-spacing: 0px;
                border-collapse: separate;
                width: 98%;     
                border-bottom: 1px solid #000;  
                border-right: 1px solid #000;
               
            }

            .table tr.header {
                background: rgb(200,200,200);
                font-weight: bold;
               
            }

  
            .table tr {
                
            }

            .table tr td {
                padding: 5px;
                border: 1px solid #000;
                border-right: none;
                overflow-wrap: break-word;
            }

            .bold {
                font-weight: bold;
            }

            .bg-light {
                background: rgba(255, 250, 200, 0.3);
            }

            header p {
                
            }

            footer { 
                position: fixed; 
                top: 90%;
                bottom: 0; 
                left: 0px; 
                right: 0px; 
                height: 5%; 
                padding: 10px;
                padding-left: 3.5%;
                padding-right: 5%;
                box-sizing: border-box;
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
            $table.="<td class='text-center' colspan='2'>{$val}</td>";
        }

        for($x = 0; $x < (count($this->year_plan_names)-5); $x++) {
            $table.="<td class='bordered text-center' colspan='2'>&nbsp;</td>";
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
            $table.="<td class='bordered text-center'>PESO</th>
            <td class='bordered text-center'>DOLLAR</th>";
        }

        for($x = 0; $x < (count($this->year_plan_names)-5); $x++) {
            $table.="<td class='bordered text-center'>&nbsp;</th>
            <td class='bordered text-center'>&nbsp;</th>";
        }

        return $table;
    }


    /**
     * 
     */
    public function get_fy_td_amount ($peso = '', $dollar = '') {

        return "<td class='bordered text-center'>{$peso}</td>
                <td  class='bordered text-center'>{$dollar}</td>";
    }

    public function get_cost_center_data ($id, $uid) {
        $__fyps = self::get_year_plan($id);
        
        # user info
        $__account_info = (self::get_user_details($uid));
        if(!isset($__account_info[0])) exit;
        if(empty($__fyps)) exit;
        $this->department_name = $__account_info[0]->fullname;

        # merge
        $this->ast = $this->isMOOE == true ? $this->merger->merge($__fyps, $uid, $this->exluded_line_items) : $this->merger->merge($__fyps, $uid);

        foreach($this->ast as $key => $value) {
            $table = ''; 
            $table.="<tr>
                        <td>{$key}</td>";
           
           $__total_peso = $__total_dollar =0;
           foreach($this->year_plan_names as $key => $val) {

               # amount
                $__peso = @$value[$val]->total_peso;
                $__dollar = @$value[$val]->total_dollar;
                $__peso_formatted = $__peso < 1 ? '' : number_format($__peso, 0, '.' , ',');
                $__dollar_formatted = $__dollar < 1 ? '' : number_format($__dollar, 0, '.' , ',');

                # Total
                $__total_peso += $__peso;
                $__total_dollar += $__dollar;

                $table.="<td class='bordered text-center'><b>{$__peso_formatted}</b></td>
                <td class='bordered text-center'><b>{$__dollar_formatted}</b></td>";
            }

            for($x = 0; $x < (count($this->year_plan_names)-5); $x++) {
                $table.="<td class='bordered text-center'>&nbsp;</td>
                <td class='bordered text-center'>&nbsp;</td>";
            }

            
            # TOTAL
            $__total_peso_formatted = $__total_peso < 1 ? '' : number_format($__total_peso, 0, '.' , ',');
            $__total_dollar_formatted = $__total_dollar < 1 ? '' : number_format($__total_dollar, 0, '.' , ',');

            $table.="<td class='bordered text-center'><b>{$__total_peso_formatted}</b></td>
                <td class='bordered text-center'><b>{$__total_dollar_formatted}</b></td>";

            # consolidated
            $__total_peso_consolidated = $__total_peso + ($__total_dollar * $value[$val]->exchangerate);
            $__total_dollar_consolidated = $__total_dollar + ($__total_peso / $value[$val]->exchangerate);

            $__total_peso_consolidated_formatted = $__total_peso_consolidated < 1 ? '' : number_format($__total_peso_consolidated, 0, '.' , ',');
            $__total_dollar_consolidated_formatted = $__total_dollar_consolidated < 1 ? '' : number_format($__total_dollar_consolidated, 0, '.' , ',');

            $table.="<td class='bordered text-center bg-light'><b>{$__total_peso_consolidated_formatted}</b></td>
                <td  class='text-center bg-light'><b>{$__total_dollar_consolidated_formatted}</b></td>";
            
            $table.="</tr>";   
        }

        return $table;
    }


    /**
     * 
     */
    public function get_html ($id, $options = []) {
        
        $__fyps = self::get_year_plan($id);
        $__fy_ids = explode(',', $this->filter_accounts);

        # Main table header
        $table = "<table class='table' cellpadding='0' cellspacing='0'>";
        $table.="
   
                <tr class='header'>
                    <td width='240px' class='bordered text-center bold' rowspan='2'>COST CENTERS</th>
                ";

        # get fiscal year headers        
        $table.= self::get_fy_header_table();
                
        $table.="<td class='bordered text-center'  colspan='2'>TOTAL</td>
        <td class='bordered text-center'  colspan='2'>CONSOLIDATED TOTAL</th>";

        $table.="</tr>
                <tr class='header'>";

        $table.= self::get_fy_header_amount();

        # peso dollar for total and consolidated header
        $table.="<td class='bordered text-center'>PESO</th>
                <td class='bordered text-center'>DOLLAR</th>
                <td class='bordered text-center'>PESO</th>
                <td class='bordered text-center'>DOLLAR</th>
                </tr>
                ";
        
        foreach($this->users as $key => $value) {
            switch($this->filter) {
                case 'programs':
                    if($value->usertype == $this->programGroup) $table.=self::get_cost_center_data($id, $value->userid);   
                    break;

                case 'admin':
                    if($value->usertype == $this->adminGroup) $table.=self::get_cost_center_data($id, $value->userid);   
                    break;

                default:
                    $table.=self::get_cost_center_data($id, $value->userid);
                    break;
            }

        }


        # end of table
        $table.="</table>";

        # exchange rates
        $table.="<br/><i>(";
        foreach($this->year_plans as $key => $value) {
            $table.="FY {$value->yeardesc} Exchange Rate: $1.00 = {$value->exchangerate};&nbsp;&nbsp;";
        }
        $table.=")</i>";

        
        $mooeTitle = $this->isMOOE == true ? '<b>(MOOE Only)</b>' : '';
        $filterTitle = ($this->filter == 'all') ? '' : (($this->filter == 'admin') ? '(ADMIN)' : '(PROGRAMS)') ;
        $html = "<html>
            <head>
                <title>Details of Total Budget</title>
            {$this->style}</head>
            <body>
            <header>
                <img src='{$this->logo}' width='120px'/>
                <article>
                    <section style='width:100%;height:20px;font-size:13px;'><br/>
                        <b>FYDP Total Budgetary Requirements Per Cost Center {$filterTitle} {$mooeTitle}</b><br/>
                            Five Year Plan {$this->fyp_details[0]->fyp_desc}<br/>
                            <small><b>(Peso & Dollar)</b></small><br/><br/><p><hr/></p>
                    </section>     
                </article>
            </header>

            
            <footer>
                <hr/>
                <div style='position: relative;'>
                    <div style='width: 30%;'>Budget Projections - {$this->date} </div>
                    <div style='width: 20%;position: absolute;top:0;left:42%;text-align:center;'>Management Services Unit<br/> SEAMEO SEARCA </div>
                </div>
                
            </footer>

            <main>{$table}</main>

            </body>
            </html>";
        return $html;
    }

    public function print($fy, $options = [], Request $request) {
        $this->filter = $request->query('filter', 'all');
        $this->isMOOE =  $request->query('mooe', false);

        // instantiate and use the dompdf class
        $dompdf = new Dompdf(array('enable_remote' => true));
        // options
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('defaultFont', 'Helvetica');
        $dompdf->set_option("isPhpEnabled", true);

        $dompdf->loadHtml(self::get_html($fy, $options));

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        $dompdf->render();

        //page number
        $dompdf->getCanvas()->page_text(735, 552, "Page {PAGE_NUM}/{PAGE_COUNT}", '', 8, array(0,0,0));
  
        // Output the generated PDF to Browser
        $dompdf->stream("dompdf_out.pdf", array("Attachment" => false));
    }
}
