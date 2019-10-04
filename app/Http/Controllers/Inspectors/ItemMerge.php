<?php

namespace App\Http\Controllers\inspectors;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mergers\Item as ItemMerger;
use App\Http\Controllers\Account as Account;

class ItemMerge extends Controller
{
    function __construct () {
        $this->merger = new ItemMerger();
        $this->account = new Account();
        $this->department_name = '';
    }

    public function show ($fy, $id, $itemId) {
        $__result = self::run($fy, $id, $itemId);
    }

    private function get_user_details ($id) {
        return $this->account->view($id);
    }
    
    public function run ($fy, $id, $itemId) {
        $this->ast = $this->merger->merge($fy, $id, $itemId);
        $__account_info = (self::get_user_details($id));

        if(!isset($__account_info[0])) { 
            echo 'Invalid Account';
            exit;
        }
    }

   
}
