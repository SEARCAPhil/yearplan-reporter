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


    public function run ($fy, $id, $itemId) {  

        $this->ast = $this->parser->run($fy, $id, $itemId);
        # cost center
        if(!isset($this->parsedAST[$id])) $this->parsedAST[$id] = new \StdClass();
        foreach($this->ast->line_items as $key => $val) { 
            # line item
            if(!isset($this->parsedAST[$id]->$key)) $this->parsedAST[$id]->$key = new \StdClass();
            # year
            $year = $val->fy->yeardesc;
            if(!isset($this->parsedAST[$id]->$key->$year)) $this->parsedAST[$id]->$key->$year = new \StdClass();
            if(!isset($this->parsedAST[$id]->$key->$year->activities)) $this->parsedAST[$id]->$key->$year->activities = [];

            foreach($val->fy->activities as $activity_key => $activity_val) { 
               if(!isset($this->parsedAST[$id]->$key->$year->activities[$activity_key])) $this->parsedAST[$id]->$key->$year->activities[$activity_key] = [];

               foreach($activity_val as $act_key_budg => $act_val_budg)
               array_push($this->parsedAST[$id]->$key->$year->activities[$activity_key], $act_val_budg); 
            }
        }

    }
}
