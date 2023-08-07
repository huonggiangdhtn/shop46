<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $fillable = ['title','address', 'description', 'status'];
    public static function deleteWarehouse($cid){
        $warehouse = Warehouse::find($cid);
        if(  0) //kiem tra cac rang buoc co nguoi dung nao dang thuoc nhom nay khong
        
            return 0;
        else
        {
           //kiem tra cac rang buoc khac phieu nhap kho xuat kho 
           $warehouse->delete();
           return 1;
        }
    }
}
