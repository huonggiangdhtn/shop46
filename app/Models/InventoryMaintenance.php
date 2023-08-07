<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMaintenance extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'quantity' ];
    public static function addPro($product_id,$quantity)
    {
        $minventory = \App\Models\InventoryMaintenance::where('product_id',$product_id)
        ->first();
        if ($minventory)
        {
            $minventory->quantity += $quantity;
            $minventory->save();
        }
        else
        {
            $minven['product_id'] = $product_id;
            $minven['quantity'] =  $quantity;
            $minventory = \App\Models\InventoryMaintenance::create($minven);
        }
    }
    public static function removePro($product_id,$quantity)
    {
        $minventory = \App\Models\InventoryMaintenance::where('product_id',$product_id)
        ->first();
        if ($minventory)
        {
            $minventory->quantity -= $quantity;
            $minventory->save();
        }
        
    }
    public static function backPro($product_id,$quantity,$extra_cost,$supplier_id)
    {
        $minventory = \App\Models\InventoryMaintenance::where('product_id',$product_id)
        ->first();
        if ($minventory)
        {
            $minventory->quantity += $quantity;
            $minventory->save();
        }
        else
        {
            $minven['product_id'] = $product_id;
            $minven['quantity'] =  $quantity;
            $minventory = \App\Models\InventoryMaintenance::create($minven);
        }
        //update maintainin sent
        $query= "select a.id from (select  * from maintain_sent_details where product_id = ".$product_id." and back < quantity ) as a left join  maintain_sents as b on a.ms_id = b.id  where b.supplier_id = ".$supplier_id." order by  id asc";
        $details = \DB::select($query);
        $in_ids=array();
        $ms_id = 0;
        foreach ($details as $dt)
        {
            // return 'dt'.$dt->id;
            $in_id = new IDs();
            $detail_sent = \App\Models\MaintainSentDetail::find($dt->id);
            if($detail_sent->quantity -($detail_sent->back+ $quantity) > 0)
            {
                $detail_sent->back += $quantity;
                $in_id->id = $detail_sent->id;
                $in_id->qty  =$quantity;
                array_push($in_ids, $in_id);
                $quantity = 0;
            }
            else
            {
                $in_id->id = $detail_sent->id;
                $in_id->qty  = ($detail_sent->quantity - $detail_sent->back);
                array_push($in_ids, $in_id);
                $quantity= $quantity- ($detail_sent->quantity - $detail_sent->back);
                $detail_sent->back = $detail_sent->quantity;
                
            }
            $detail_sent->save();
            $ms_id = $detail_sent->ms_id;
            ///update ms record if all detail back
            $ms_details = \App\Models\MaintainSentDetail::where('ms_id',$ms_id);
            $flag = 0;
            foreach ($ms_details as $ms_detail)
            {
                if($ms_detail->back < $ms_detail->quantity)
                {
                    $flag = 1;
                    break;
                }
            }
            if($flag == 0)
            {
                $ms = \App\Models\MaintainSent::find($ms_id);
                $ms->status = "back";
                $ms->save();
            }
            ////////////////////////////////////////
            $in_back_ids = json_decode( $detail_sent->in_ids);
            $temp = $in_id->qty;
            foreach ($in_back_ids as $in_back_id)
            {
                $detail_in = \App\Models\MaintenanceIn::find($in_back_id->id);
                
                if($detail_in)
                {
                    $detail_in->status = "back";
                    if ( $temp  > $in_back_id->qty)
                     {
                        $detail_in->final_amount += $extra_cost * $in_back_id->qty;
                        $temp -= $in_back_id->qty;

                     }   
                    else
                    {
                        $detail_in->final_amount += $extra_cost * $temp   ;
                        $temp = 0;
                    }   
                    $detail_in->save();
                    if($temp == 0)
                        break;
                }
            }
            if($quantity == 0)
                break;
        }
        return $in_ids;
        
    }
    public static function deletebackPro($back_detail,$extra_cost )
    {
        $minventory = \App\Models\InventoryMaintenance::where('product_id',$back_detail->product_id)
        ->first();
        if ($minventory)
        {
            $minventory->quantity -= $back_detail->quantity;
            $minventory->save();
        }
        //update maintainin sent
        $in_ids=json_decode($back_detail->in_ids);
        $temp =$back_detail->quantity ;
        foreach ($in_ids  as $in_id)
        {
            // return 'dt'.$dt->id;
            $detail_sent = \App\Models\MaintainSentDetail::find($in_id->id);
            $detail_sent->back -= $in_id->qty;
            $detail_sent->save();
            $in_back_ids = json_decode( $detail_sent->in_ids);
            
            foreach ($in_back_ids as $in_back_id)
            {
                $detail_in = \App\Models\MaintenanceIn::find($in_back_id->id*1);
                if($detail_in)
                {
                    $detail_in->status = "sent";
                    if ( $temp> $in_back_id->qty)
                     {
                        $detail_in->final_amount -= $extra_cost * $in_back_id->qty;
                        $temp -= $in_back_id->qty;
                     }  
                    else
                     {
                        $detail_in->final_amount -= $extra_cost * $back_detail->quantity ;
                        $temp= 0;
                     }   
                 
                    $detail_in->save();
                    if ($temp == 0)
                        break;
                }
                  $ms_id = $detail_sent->ms_id;
            ///update ms record if all detail back
             
            $ms = \App\Models\MaintainSent::find($detail_sent->ms_id);
            $ms->status = "sent";
            $ms->save();
             
            ////////////////////////////////////////
            }
        }
        $back_detail->delete();
    }
    public static function sendPro($product_id,$quantity,$extra_cost)
    {
        $minventory = \App\Models\InventoryMaintenance::where('product_id',$product_id)
        ->first();
        if ($minventory)
        {
            $minventory->quantity -= $quantity;
            $minventory->save();
        }
        //update maintainin sent
        $query= "select * from maintenance_ins where product_id = ".$product_id." and sent < quantity order by  id asc";
        $details = \DB::select($query);
        $in_ids=array();
        foreach ($details as $dt)
        {
            // return 'dt'.$dt->id;
            $in_id = new IDs();
            $detail = \App\Models\MaintenanceIn::find($dt->id);
            if($detail->quantity >= $detail->sent+ $quantity)
            {
                $detail->sent += $quantity;
                $detail->status = 'sent';
                $detail->final_amount += $extra_cost *$quantity  ;
                $in_id->id = $detail->id;
                $in_id->qty  = $quantity;
                array_push($in_ids, $in_id);
                $quantity = 0;
            }
            else
            {
               
                $in_id->id = $detail->id;
                $in_id->qty  = ($detail->quantity - $detail->sent);
                $detail->status = 'sent';
                array_push($in_ids, $in_id);
                $quantity= $quantity- ($detail->quantity - $detail->sent);
                $detail->sent = $detail->quantity;
                $detail->final_amount += $extra_cost *$in_id->qty ;
            }
            $detail->save();
           
           
            if($quantity == 0)
                break;
        }
        return $in_ids;
        
    }
    public static function deletesendPro($detail,$extra_cost)
    {
        $minventory = \App\Models\InventoryMaintenance::where('product_id',$detail->product_id)
        ->first();
        if ($minventory)
        {
            $minventory->quantity += $detail->quantity;
            $minventory->save();
        }
        //update maintainin sent
       
        $in_ids=json_decode($detail->in_ids);
        
        foreach ($in_ids as $in_id)
        {
            $detail_in = \App\Models\MaintenanceIn::find($in_id->id);
            $detail_in->sent -= $in_id->qty;
            $detail_in->final_amount -= $extra_cost*$in_id->qty;
            $detail_in->save();
        } 
        $detail->delete();
    }
}
