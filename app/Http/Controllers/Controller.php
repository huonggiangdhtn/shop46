<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public function checkRole($level)
    {
       
        $roles = ['admin','manager','vendor','supplier','customer','supcustomer'];
        $user = auth()->user();
        // echo 'check role:'.$user->role;
        if($level == 1)
        {
            if($user->role != 'admin' )
            {
               return 0;
            }
            else
                return 1;
        }
        
    }
    public function unauthorized()
    {
        $active_menu="dashboard";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>';
         
        
        return view('backend.auth',compact('breadcrumb', 'active_menu' ));

    }
    public function absnumber($number)
    {

        if($number < 0)
            $number = -$number;
        return $number;
    }
}
