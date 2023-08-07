<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\IDs;
class Inventory extends Model
{
    use HasFactory;
    protected $fillable = ['product_id','wh_id','quantity' ];
    public static function addProduct($pro_id, $wh_id,$qty,$price,$cost_extra){
        $inventory = Inventory::where("product_id",$pro_id)->where('wh_id',$wh_id)->first();
        if($inventory)
        {
            $inventory->quantity += $qty;
            $inventory->save();
        }
        else
        {
            $data['product_id'] = $pro_id;
            $data['wh_id'] = $wh_id;
            $data['quantity'] = $qty;
            Inventory::create($data);
        }
        $product = Product::where("id",$pro_id)->first();
        if($product)
        {
           
            $product->price_in = $price;
            $avg = (int) (($product->price_avg * $product->stock + ( $product->price_in + $cost_extra)*$qty)
                            /($product->stock + $qty));
            $product->stock += $qty;
            $product->price_avg = $avg;
            $product->save();
        }
    }
    public static function subProduct($pro_id, $wh_id,$qty,$price,$cost_extra)
    {
        $inventory = Inventory::where("product_id",$pro_id)->where('wh_id',$wh_id)->first();
        if($inventory)
        {
            $inventory->quantity -= $qty;
            $inventory->save();
        }
       
        $product = Product::where("id",$pro_id)->first();
        if($product)
        {
            $avg = (int) (($product->price_out * $product->sold + ( $price - $cost_extra)*$qty)
                            /($product->sold + $qty));
            $product->stock -= $qty;
            $product->sold += $qty;
            $product->price_out = $avg;
            $product->save();
        }
        // $query = "(select warehouse_in_details.* from `warehouse_in_details` left join (select id as wid from warehouse_ins where wh_id ="
        // .$wh_id.") as b on `warehouse_in_details`.`wi_id` = `b`.`wid` where `product_id` = ".$pro_id." and `qty_sold` < quantity order by warehouse_in_details.id asc)";
        $query= "select * from warehouse_in_details where product_id = ".$pro_id." and wh_id = ".$wh_id." and qty_sold < quantity order by warehouse_in_details.id asc";
        $details = DB::select($query);
        // $details = WarehouseInDetail::where('wh_id',$wh_id)->where('product_id',$pro_id)
        // ->where('qty_sold','<','quantity')->orderBy('id','ASC')->get();
        $in_ids=array();
        
        foreach ($details as $dt)
        {
            // return 'dt'.$dt->id;
            $in_id = new IDs();
            $detail = \App\Models\WarehouseInDetail::find($dt->id);
            if($detail->quantity >= $detail->qty_sold+ $qty)
            {
                $detail->qty_sold += $qty;
                $in_id->id = $detail->id;
                $in_id->qty  = $qty;
                array_push($in_ids, $in_id);
                $qty = 0;
            }
            else
            {
               
                $in_id->id = $detail->id;
                $in_id->qty  = ($detail->quantity - $detail->qty_sold);
                
                array_push($in_ids, $in_id);
                $qty= $qty- ($detail->quantity - $detail->qty_sold);
                $detail->qty_sold = $detail->quantity;
            }
            $detail->save();
           
           
            if($qty == 0)
                break;
        }
        return $in_ids;
         
    }
public static function mainTransfer($pro_id, $wh_id,$qty,$price )
    {
        $inventory = Inventory::where("product_id",$pro_id)->where('wh_id',$wh_id)->first();
        if($inventory)
        {
            $inventory->quantity -= $qty;
            $inventory->save();
        }
       
        $product = Product::where("id",$pro_id)->first();
        if($product)
        {
            $avg = (int) (($product->price_out * $product->sold + ( $price  )*$qty)
                            /($product->sold + $qty));
            $product->stock -= $qty;
            $product->sold += $qty;
            $product->price_out = $avg;
            $product->save();
        }
        // $query = "(select warehouse_in_details.* from `warehouse_in_details` left join (select id as wid from warehouse_ins where wh_id ="
        // .$wh_id.") as b on `warehouse_in_details`.`wi_id` = `b`.`wid` where `product_id` = ".$pro_id." and `qty_sold` < quantity order by warehouse_in_details.id asc)";
        $query= "select * from warehouse_in_details where product_id = ".$pro_id." and wh_id = ".$wh_id." and qty_sold < quantity order by warehouse_in_details.id asc";
        $details = DB::select($query);
        // $details = WarehouseInDetail::where('wh_id',$wh_id)->where('product_id',$pro_id)
        // ->where('qty_sold','<','quantity')->orderBy('id','ASC')->get();
        $in_ids=array();
        
        foreach ($details as $dt)
        {
            // return 'dt'.$dt->id;
            $in_id = new IDs();
            $detail = \App\Models\WarehouseInDetail::find($dt->id);
            if($detail->quantity >= $detail->qty_sold+ $qty)
            {
                $detail->qty_sold += $qty;
                $in_id->id = $detail->id;
                $in_id->qty  = $qty;
                array_push($in_ids, $in_id);
                $qty = 0;
            }
            else
            {
               
                $in_id->id = $detail->id;
                $in_id->qty  = ($detail->quantity - $detail->qty_sold);
                
                array_push($in_ids, $in_id);
                $qty= $qty- ($detail->quantity - $detail->qty_sold);
                $detail->qty_sold = $detail->quantity;
            }
            $detail->save();
           
           
            if($qty == 0)
                break;
        }
        return $in_ids;
         
    }
    public static function transfer($wti_id,$pro_id, $wh_id1,$wh_id2,$qty,$price,$cost_extra)
    {
        $inventory1 = Inventory::where("product_id",$pro_id)->where('wh_id',$wh_id1)->first();
        if($inventory1)
        {
            $inventory1->quantity -= $qty;
            $inventory1->save();
        }
        $inventory2 = Inventory::where("product_id",$pro_id)->where('wh_id',$wh_id2)->first();
        if($inventory2)
        {
            $inventory2->quantity += $qty;
            $inventory2->save();
        }
        else
        {
            $data_i['product_id'] = $pro_id;
            $data_i['wh_id'] = $wh_id2;
            $data_i['quantity'] = $qty;
            $inventory2=Inventory::create($data_i);
        }
        $product = Product::where("id",$pro_id)->first();
        if($product)
        {
            $product->price_avg = (int) (($product->price_avg * $product->stock +   $cost_extra *$qty)
                            /($product->stock));
            $product->save();
        }

        // $query = "select * from wwhere `product_id` = ".$pro_id." and `qty_sold` < quantity order by warehouse_in_details.id asc)";
        $query= "select * from warehouse_in_details where product_id = ".$pro_id." and wh_id = ".$wh_id1." and qty_sold < quantity order by warehouse_in_details.id asc";
        $details = DB::select($query);
        
        
        foreach ($details as $dt)
        {
            // return 'dt'.$dt->id;
            $in_id = new IDs();
            $detail = \App\Models\WarehouseInDetail::find($dt->id);
            if($detail->quantity >= $detail->qty_sold+ $qty)
            {
                $detail->qty_sold += $qty;
                //save TransferDetail
                $trans['wi_id'] = 0;
                $trans['wti_id'] = $wti_id;
                $trans['wh_id'] = $wh_id2;
                $trans['product_id'] = $pro_id;
                $trans['quantity'] = $qty;
                $trans['price'] =  $price;
                $trans['qty_sold'] = 0;
                $trans['expired_at'] = $detail->expired_at;
                WarehouseInDetail::create($trans);
                $qty = 0;
            }
            else
            {
               
                //save TransferDetail
                $trans['wi_id'] = 0;
                $trans['wti_id'] = $wti_id;
                $trans['product_id'] = $pro_id;
                $trans['quantity'] = ($detail->quantity - $detail->qty_sold);
                $trans['price'] =  $price;
                $trans['qty_sold'] = 0;
                $trans['wh_id'] = $wh_id2;
                $trans['expired_at'] = $detail->expired_at;
                WarehouseInDetail::create($trans);
                $qty= $qty- ($detail->quantity - $detail->qty_sold);
                $detail->qty_sold = $detail->quantity;
            }
            $detail->save();
           
           
            if($qty == 0)
                break;
        }
        
         
    }
}
