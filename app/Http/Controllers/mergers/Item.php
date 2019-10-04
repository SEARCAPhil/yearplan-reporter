<?php

namespace App\Http\Controllers\mergers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Parsers\Item as ItemParser;

class Item extends Controller
{
    function __construct () {
        $this->parser = new ItemParser();
        $this->parsedAST = [];   
    }

    
    public function show ($fy, $id) {
        $__result = self::merge($fy, $id);
    }


    public function merge ($fy, $id ,$itemId) {

        $__fys = explode(',', $fy);
        foreach($__fys as $key => $val) { 
            self::run((int) $val, $id, $itemId);
        }
        
        return $this->parsedAST;
    }


    public function preParsed ($fy, $id, $itemId) {
        /**
         * FORMAT
         * Line Item
         *      Fiscal YEar
         *          Activities
         */

        # get data
        $this->__ast = $this->parser->run($fy, $id, $itemId);
        $this->preParsedAST = [];
        var_dump($this->__ast);
 
        # line items
        foreach($this->__ast as $line_key => $line_val) { 
           /* $this->preParsedAST[$line_key] = [];
            # line items -> fiscal year
            if(!isset($this->preParsedAST[$line_key][$val->yeardesc])) $this->preParsedAST[$line_key][$val->yeardesc] = [];
            $this->preParsedAST[$line_key][$val->yeardesc] = $line_val; */
        }
        
exit;
        return $this->preParsedAST;

    }

    public function run ($fy, $id, $itemId) {  

        $this->ast = self::preParsed($fy, $id, $itemId);

        foreach($this->ast as $key => $val) { 
            # line item
            if(!isset($this->parsedAST[$key])) $this->parsedAST[$key] = [];

            foreach($val as $fy_key => $fy_val) {
                if(!isset($this->parsedAST[$key][$fy_key])) $this->parsedAST[$key][$fy_key] = new \StdClass();
                
                # activities
                if(!isset($this->parsedAST[$key][$fy_key]->activities)) $this->parsedAST[$key][$fy_key]->activities = [];
                $this->parsedAST[$key][$fy_key]->activities[] = $fy_val->activities;
            }

        }
    }
}
