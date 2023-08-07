<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseoutDetail extends Model
{
    use HasFactory;
    protected $fillable = ['wo_id', 'wfo_id','product_id', 'quantity','price','expired_at','in_ids'];
    public static function deleteDetailPro($detailpro,$extraprice,$wh_id)
    {
        $product = \App\Models\Product::where('id',$detailpro->product_id)->first();
        if($product->sold ==$detailpro->quantity )
        {
            $avg = 0;
        }
        else
        {
            $avg =  $product->sold * $product->price_out - ($detailpro->price -$extraprice )*$detailpro->quantity;
            $avg = $avg/($product->sold - $detailpro->quantity);
        }
        $product->sold -= $detailpro->quantity;
        $product->stock += $detailpro->quantity;
        $product->price_out = $avg;
        
        $inventory = \App\Models\Inventory::where('product_id',$detailpro->product_id)
            ->where('wh_id',$wh_id)->first();
        $inventory->quantity += $detailpro->quantity;
        $product->save();
        $inventory->save();
        //return product to warehouseindetail
        $in_ids = json_decode($detailpro->in_ids);
        foreach ($in_ids as $in_id)
        {
            $detail_in = \App\Models\WarehouseInDetail::find($in_id->id);
            $detail_in->qty_sold -= $in_id->qty;
            $detail_in->save();
        } 
        ///
        $detailpro->delete();
        
    }
    public static function returnDetailPro($detailpro,$extraprice,$wh_id)
    {
        $product = \App\Models\Product::where('id',$detailpro->product_id)->first();
        if($product->sold ==$detailpro->quantity )
        {
            $avg = 0;
        }
        else
        {
            $avg =  $product->sold * $product->price_out - ($detailpro->price -$extraprice )*$detailpro->quantity;
            $avg = $avg/($product->sold - $detailpro->quantity);
        }
        $product->sold -= $detailpro->quantity;
        $product->stock += $detailpro->quantity;
        $product->price_out = $avg;
        
        $inventory = \App\Models\Inventory::where('product_id',$detailpro->product_id)
            ->where('wh_id',$wh_id)->first();
        $inventory->quantity += $detailpro->quantity;
        $product->save();
        $inventory->save();
        //return product to warehouseindetail
        $in_ids = json_decode($detailpro->in_ids);
        foreach ($in_ids as $in_id)
        {
            $detail_in = \App\Models\WarehouseInDetail::find($in_id->id);
            $detail_in->qty_sold -= $in_id->qty;
            $detail_in->save();
        } 
        ///
        // $detailpro->delete();
        
    }
}
