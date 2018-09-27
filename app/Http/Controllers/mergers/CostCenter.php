<?php

namespace App\Http\Controllers\Mergers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Parsers\CostCenter as CostCenterParser;
use App\Http\Controllers\Account as Account;

class CostCenter extends Controller
{
    function __construct () {
        $this->parser = new CostCenterParser();
        $this->account = new Account();
        $this->parsedAST = [];
        $this->excluded_line_items = [];   
    }

    private function get_user_details ($id) {
        return $this->account->view($id);
    }

    public function show ($fy, $id) {
        $__result = self::merge($fy, $id);
    }


    public function merge ($fy, $id, $excluded_line_items = []) {
        $this->excluded_line_items = $excluded_line_items;

        $__fys = explode(',', $fy);
        foreach($__fys as $key => $val) { 
            self::run((int) $val, $id);
        }
        
        return $this->parsedAST;
    }


    public function preParsed ($fy, $id) {
        /**
         * FORMAT
         * Cost center
         *      Fiscal Year
         *          Peso | Dollar
         */

        # get data
        $this->__ast = $this->parser->run($fy, $id, $this->excluded_line_items);

        $this->preParsedAST = [];

        $__account_info = (self::get_user_details($id));

        if(!isset($__account_info[0])) { 
            echo 'Invalid Account';
            exit;
        }
        
        $this->department_name = $__account_info[0]->fullname;

        
        if (!isset($this->preParsedAST[$this->department_name])) $this->preParsedAST[$this->department_name] = [];

        # query
        foreach($this->__ast as $key => $val) { 
            # set amount
            if (!isset($this->preParsedAST[$this->department_name][$val->yeardesc])) $this->preParsedAST[$this->department_name][$val->yeardesc] = new \StdClass;
            if(!isset($this->preParsedAST[$this->department_name][$val->yeardesc]->total_peso)) $this->preParsedAST[$this->department_name][$val->yeardesc]->total_peso = $this->preParsedAST[$this->department_name][$val->yeardesc]->total_dollar = 0;
            
            $this->preParsedAST[$this->department_name][$val->yeardesc]->total_peso += $val->total_peso;
            $this->preParsedAST[$this->department_name][$val->yeardesc]->total_dollar += $val->total_dollar;
            $this->preParsedAST[$this->department_name][$val->yeardesc]->exchangerate = $val->exchangerate;
        }

        return $this->preParsedAST;

    }

    public function run ($fy, $id) {  

        $this->ast = self::preParsed($fy, $id); 

        foreach($this->ast as $key => $val) {

            # cost center
            if(!isset($this->parsedAST[$key])) $this->parsedAST[$key] = [];
            
            # Fiscal Year
            foreach($val as $key_fy => $val_fy) {
                if(!isset($this->parsedAST[$key][$key_fy]))  $this->parsedAST[$key][$key_fy] = new \StdClass;
                if(!isset($this->parsedAST[$key][$key_fy]->total_peso)) $this->parsedAST[$key][$key_fy]->total_peso = $this->parsedAST[$key][$key_fy]->total_dollar = 0;
                
                # peso
                $this->parsedAST[$key][$key_fy]->total_peso += $val_fy->total_peso;
    
                # dollar
                $this->parsedAST[$key][$key_fy]->total_dollar+= $val_fy->total_dollar;
                
                # exchangerate
                $this->parsedAST[$key][$key_fy]->exchangerate= $val_fy->exchangerate;

            }
        }
    }
}
