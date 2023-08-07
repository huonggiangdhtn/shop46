<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bankaccount extends Model
{
    use HasFactory;
    protected $fillable = ['title','banknumber','total','status' ];
    public static function deleteBankaccount($pro_id){
        $bankaccount = Bankaccount::find($pro_id);
        if(  0)
            return 0;
        else
        {
           //kiem tra cac rang buoc khac phieu nhap kho xuat kho 
           $bankaccount->delete();
           return 1;
        }
    }
}
