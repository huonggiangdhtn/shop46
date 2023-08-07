<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseInDetail extends Model
{
    use HasFactory;
    protected $fillable = ['wi_id','wti_id','wh_id', 'product_id', 'quantity','price','qty_sold','expired_at'];
    public static function deleteDetailPro($detailpro,$extraprice,$wh_id)
    {
        $product = \App\Models\Product::where('id',$detailpro->product_id)->first();
        if($product->stock ==$detailpro->quantity )
        {
            $avg = 0;
        }
        else
        {
            $avg =  $product->stock * $product->price_avg - ($extraprice + $detailpro->price)*$detailpro->quantity;
            $avg = $avg/($product->stock - $detailpro->quantity);
        }
        $product->stock -= $detailpro->quantity;
        $product->price_avg = $avg;
        
        $inventory = \App\Models\Inventory::where('product_id',$detailpro->product_id)
            ->where('wh_id',$wh_id)->first();
        $inventory->quantity -= $detailpro->quantity;
        $product->save();
        $inventory->save();
        $detailpro->delete();
    }
    public static function returnDetailPro($detailpro,$extraprice,$wh_id)
    {
        $product = \App\Models\Product::where('id',$detailpro->product_id)->first();
        if($product->stock ==$detailpro->quantity )
        {
            $avg = 0;
        }
        else
        {
            $avg =  $product->stock * $product->price_avg - ($extraprice + $detailpro->price)*$detailpro->quantity;
            $avg = $avg/($product->stock - $detailpro->quantity);
        }
        $product->stock -= $detailpro->quantity;
        $product->price_avg = $avg;
        
        $inventory = \App\Models\Inventory::where('product_id',$detailpro->product_id)
            ->where('wh_id',$wh_id)->first();
        $inventory->quantity -= $detailpro->quantity;
        $product->save();
        $inventory->save();
        // $detailpro->delete();
    }
    public static function deleteDetailTransfer($detailpro,$extraprice,$wh_id1,$wh_id2)
    {
        $product = \App\Models\Product::where('id',$detailpro->product_id)->first();
        if($product->stock ==$detailpro->quantity )
        {
            $avg = 0;
        }
        else
        {
            $avg =  $product->stock * $product->price_avg - ($extraprice )*$detailpro->quantity;
            $avg = $avg/($product->stock );
        }
        
        $product->price_avg = $avg;
        
        $inventory = \App\Models\Inventory::where('product_id',$detailpro->product_id)
            ->where('wh_id',$wh_id1)->first();
        $inventory->quantity += $detailpro->quantity;
        $inventory2 = \App\Models\Inventory::where('product_id',$detailpro->product_id)
        ->where('wh_id',$wh_id2)->first();
        $inventory2->quantity -= $detailpro->quantity;


        $product->save();
        $inventory->save();
        $inventory2->save();

        $query= "select * from warehouse_in_details where product_id = ".$detailpro->product_id." and wh_id = ".$wh_id1." and expired_at = '".$detailpro->expired_at."' order by warehouse_in_details.id asc";
        $details = \DB::select($query);
        
        foreach ($details as $dt)
        {
            // return 'dt'.$dt->id;
            $detail = \App\Models\WarehouseInDetail::find($dt->id);
            if($detail->qty_sold - $detailpro->quantity >= 0)
            {
                $detail->qty_sold -= $detailpro->quantity;
                $detailpro->quantity = 0;
            }
            else
            {
                
                $detail->quantity -= $detail->qty_sold;
                $detail->qty_sold = 0;
            }
            $detail->save();
           
           
            if($detailpro->quantity == 0)
                break;
        }
        $detailpro->delete();
    }
}
