<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseIn extends Model
{
    use HasFactory;
    protected $fillable = ['wh_id', 'supplier_id', 'vendor_id','final_amount','discount_amount','paid_amount','is_paid','suptrans_id','paidtrans_id','shiptrans_id','cost_extra'];
   
}
