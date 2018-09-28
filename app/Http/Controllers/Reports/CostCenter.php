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

        # defaults
        $this->users = self::get_all_users();
        $this->style = self::get_style();
        $this->line_items = self::get_line_items();
        $this->date = date('M-d-Y');  
        $this->logo = 'img/logo.png';
        $this->year_plan_names = [];

        # filter by user types
        $this->adminGroup = 1;
        $this->programGroup = 2;

        # mode
        $this->isMOOE = false;

        # Exclude line items and cost center in the report
        $this->exluded_line_items = ['user-centric maximized database'];
        $this->exluded_cost_centers = [35, 29, 4, 3, 14, 20, 30, 32, 36];

        # amounts      
        $this->total_amounts = [];  
        $this->grand_total_peso = $this->grand_total_dollar = $this->grand_total_peso_consolidated = $this->grand_total_dollar_consolidated = 0;  
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

        $this->year_plans = ($this->year_plan->get_yearplan_per_fyp ($id));
        $__year_plans_ids = [];
        $this->year_plan_names = [];
        $this->fyp_details = $this->fyp->view($id);

        foreach($this->year_plans as $key => $val) {
            array_push($this->year_plan_names, $val->yeardesc);
            if($val->yearplanid)  array_push($__year_plans_ids, $val->yearplanid);
        }

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
                background: #ccc;
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
                background: #f0ad4e;
            }

            .bg-red {
                background: #d9534f;
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
    private function get_fy_header_table () {
        
        # Fiscal Year Headers
        $__table = '';
        foreach($this->year_plan_names as $key => $val) $__table.="<td class='text-center' colspan='2'>{$val}</td>";

        for($x = 0; $x < (count($this->year_plan_names)-5); $x++) $__table.="<td class='bordered text-center' colspan='2'>&nbsp;</td>";

        return $__table;
    }


    /**
     * 
     */
    public function get_fy_header_amount () {
        
        # Fiscal Year Headers
        $__table = '';
        foreach($this->year_plan_names as $key => $val) $__table.="<td class='bordered text-center'>PESO</td><td class='bordered text-center'>DOLLAR</td>";

        for($x = 0; $x < (count($this->year_plan_names)-5); $x++) $__table.="<td class='bordered text-center'>&nbsp;</td><td class='bordered text-center'>&nbsp;</td>";
    
        return $__table;
    }


    /**
     * 
     */
    public function get_fy_total_column () {
        $table="<tr style='background: #eee;' class='bold'><td class='bordered text-center bold'>TOTAL</td>";

        # Fiscal Year Headers
        foreach($this->year_plan_names as $key => $val) { 
            $__peso = isset($this->total_amounts[$val]) ? $this->total_amounts[$val]->peso : 0;
            $__peso_formatted = $__peso < 1 ? '' : number_format($__peso, 0, '.' , ',');

            $__dollar = isset($this->total_amounts[$val]) ? $this->total_amounts[$val]->dollar : 0;
            $__dollar_formatted = $__dollar < 1 ? '' : number_format($__dollar, 0, '.' , ',');

            $table.="<td class='bordered text-right'>{$__peso_formatted}</td>
            <td class='bordered text-right'>{$__dollar_formatted}</th>";
        }

        for($x = 0; $x < (count($this->year_plan_names)-5); $x++) {
            $table.="<td class='bordered text-center'>&nbsp;</td>
            <td class='bordered text-center'>&nbsp;</td>";
        }

        # GRAND TOTAL
        $this->grand_total_peso_formatted = $this->grand_total_peso < 1 ? '' : number_format($this->grand_total_peso, 0, '.' , ',');
        $this->grand_total_dollar_formatted = $this->grand_total_dollar < 1 ? '' : number_format($this->grand_total_dollar, 0, '.' , ',');

        # GRAND TOTAL CONSOLIDATED
        $this->grand_total_peso_consolidated_formatted = $this->grand_total_peso_consolidated < 1 ? '' : number_format($this->grand_total_peso_consolidated, 0, '.' , ',');
        $this->grand_total_dollar_consolidated_formatted = $this->grand_total_dollar_consolidated < 1 ? '' : number_format($this->grand_total_dollar_consolidated, 0, '.' , ',');


        $table.="<td class='bordered text-right'>{$this->grand_total_peso_formatted}</td>
        <td class='bordered text-right'>{$this->grand_total_dollar_formatted}</td>";

        $table.="<td class='bordered text-right bg-red'>{$this->grand_total_peso_consolidated_formatted}</td>
        <td class='bordered text-right bg-red'>{$this->grand_total_dollar_consolidated_formatted}</td></tr>";

        return $table;
    }


    /**
     * 
     */
    public function get_fy_td_amount ($peso = '', $dollar = '') {
        return "<td class='bordered text-center'>{$peso}</td><td class='bordered text-center'>{$dollar}</td>";
    }

    public function get_cost_center_data ($id, $uid) {
        # get all fyp
        $__fyps = self::get_year_plan($id);
        
        # user info
        $__account_info = (self::get_user_details($uid));
        if(!isset($__account_info[0])) exit;
        if(empty($__fyps)) exit;

        # merge and exclude certain line items if mooe is set to true
        # Note: adding mooe=true in url query parameter will override $this->mooe default value
        $this->ast = $this->isMOOE == true ? $this->merger->merge($__fyps, $uid, $this->exluded_line_items) : $this->merger->merge($__fyps, $uid);

        $__peso_column = $__dollar_column  =  $__peso_column_consolidated = $__dollar_column_consolidated = 0;
        $__total_amount_per_column = [];

        foreach($this->ast as $key => $value) {

            $__table = "<tr><td>{$__account_info[0]->alias}</td>";
            $__total_peso = $__total_dollar = 0;

            foreach($this->year_plan_names as $keys => $val) { 
                
                # amount
                $__peso = @$value[$val]->total_peso;
                $__dollar = @$value[$val]->total_dollar;
                $__peso_formatted = $__peso < 1 ? '' : number_format($__peso, 0, '.' , ',');
                $__dollar_formatted = $__dollar < 1 ? '' : number_format($__dollar, 0, '.' , ',');

                # total amount per column
                if(!isset($__total_amount_per_column[$val]))  $__total_amount_per_column[$val] = new \StdClass;
                if(!isset($__total_amount_per_column[$val]->peso))  $__total_amount_per_column[$val]->peso =  $__total_amount_per_column[$val]->dollar = 0;
                $__total_amount_per_column[$val]->peso += $__peso;
                $__total_amount_per_column[$val]->dollar += $__dollar;

            
                # total amount per row
                $__total_peso += $__peso;
                $__total_dollar += $__dollar;

                # amount per column
                $__table.="<td class='bordered text-right'><b>{$__peso_formatted}</b></td><td class='bordered text-right'><b>{$__dollar_formatted}</b></td>";
            }

            $this->total_amounts =  $__total_amount_per_column;
 
            for($x = 0; $x < (count($this->year_plan_names)-5); $x++) {
                $__table.="<td class='bordered text-center'>&nbsp;</td><td class='bordered text-center'>&nbsp;</td>";
            }
               
            
            # TOTAL
            $__total_peso_formatted = $__total_peso < 1 ? '' : number_format($__total_peso, 0, '.' , ',');
            $__total_dollar_formatted = $__total_dollar < 1 ? '' : number_format($__total_dollar, 0, '.' , ',');

            # grand total
            $__peso_column +=$__total_peso;
            $__dollar_column +=$__total_dollar;
            $this->grand_total_peso = $__peso_column;
            $this->grand_total_dollar = $__dollar_column;

            $__table.="<td class='bordered text-right'><b>{$__total_peso_formatted }</b></td><td class='bordered text-right'><b>{$__total_dollar_formatted}</b></td>";

            # consolidated
            $__total_peso_consolidated = $__total_peso + ($__total_dollar * $value[$val]->exchangerate);
            $__total_dollar_consolidated = $__total_dollar + ($__total_peso / $value[$val]->exchangerate);

            $__total_peso_consolidated_formatted = $__total_peso_consolidated < 1 ? '' : number_format($__total_peso_consolidated, 0, '.' , ',');
            $__total_dollar_consolidated_formatted = $__total_dollar_consolidated < 1 ? '' : number_format($__total_dollar_consolidated, 0, '.' , ',');

            # grand total consolidated
            $__peso_column_consolidated +=$__total_peso_consolidated;
            $__dollar_column_consolidated+=$__total_dollar_consolidated;
            $this->grand_total_peso_consolidated = $__peso_column_consolidated;
            $this->grand_total_dollar_consolidated = $__dollar_column_consolidated;

            $__table.="<td class='bordered text-right bg-light'><b>{$__total_peso_consolidated_formatted}</b></td>
                <td class='text-right bg-light'><b>{$__total_dollar_consolidated_formatted}</b></td>";
            
            $__table.="</tr>";   
        }

        return $__table;
    }


    /**
     * 
     */
    private function get_html ($id, $options = []) {
        
        $__fyps = self::get_year_plan($id);

        # Main table header
        $__table = "<table class='table' cellpadding='0' cellspacing='0'>";
        $__table.="<tr class='header'><td width='170px' class='bordered text-center bold' rowspan='2'>COST CENTERS</th>";

        # get fiscal year headers        
        $__table.= self::get_fy_header_table();
                
        $__table.="<td class='bordered text-center'  colspan='2'>TOTAL</td><td class='bordered text-center'  colspan='2'>CONSOLIDATED TOTAL</th>";

        $__table.="</tr><tr class='header'>";

        $__table.= self::get_fy_header_amount();

        # peso dollar for total and consolidated header
        $__table.="<td class='bordered text-center'>PESO</th>
                <td class='bordered text-center'>DOLLAR</th>
                <td class='bordered text-center'>PESO</th>
                <td class='bordered text-center'>DOLLAR</th>
                </tr>
                ";
        
        foreach($this->users as $key => $value) {
            # exclude other accounts specified by admin
            if(!in_array($value->userid, $this->exluded_cost_centers)) {
                switch($this->filter) {
                    case 'programs':
                        if($value->usertype == $this->programGroup) $__table.=self::get_cost_center_data($id, $value->userid);   
                        break;

                    case 'admin':
                        if($value->usertype == $this->adminGroup) $__table.=self::get_cost_center_data($id, $value->userid);   
                        break;

                    default:
                        $__table.=self::get_cost_center_data($id, $value->userid);
                        break;
                }
            }

        }

        # total per column
        $__table.=self::get_fy_total_column ();

        # end of table
        $__table.="</table>";

        # exchange rates
        $__table.="<br/><i>(";
        foreach($this->year_plans as $key => $value) {
            $__table.="FY {$value->yeardesc} Exchange Rate: $1.00 = {$value->exchangerate};&nbsp;&nbsp;";
        }
        $__table.=")</i>";

        
        $__mooeTitle = $this->isMOOE == true ? '<b>(MOOE Only)</b>' : '';
        $__filterTitle = ($this->filter == 'all') ? '' : (($this->filter == 'admin') ? '(ADMIN)' : '(PROGRAMS)') ;
        $__html = "<html>
            <head>
                <title>Details of Total Budget</title>
            {$this->style}</head>
            <body>
            <header>
                <img src='{$this->logo}' width='120px'/>
                <article>
                    <section style='width:100%;height:20px;font-size:13px;'><br/>
                        <b>FYDP Total Budgetary Requirements Per Cost Center {$__filterTitle} {$__mooeTitle}</b><br/>
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

            <main>{$__table}</main>

            </body>
            </html>";
        return $__html;
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
