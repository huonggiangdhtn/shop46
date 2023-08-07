<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['title','slug','summary','description','stock','sold','price_in','price_avg','price_out','brand_id','cat_id','parent_cat_id','photo','size','weight','expired','is_sold','status'];

    public static function deleteProduct($pro_id){
        $product = Product::find($pro_id);
        if($product != null && $product->stock > 0)
            return 0;
        else
        {
           //kiem tra cac rang buoc khac phieu nhap kho xuat kho 
           $product->delete();
           return 1;
        }
    }
}
 