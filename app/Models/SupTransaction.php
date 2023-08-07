<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['supplier_id','doc_id','doc_type',  'operation','amount','total'];
    public static function createSubTrans($doc_id,$doc_type,$operation,$amount, $supplier_id)
    {
         ///create SupTransaction
         $supplier = \App\Models\User::where('id',$supplier_id)->first();
         $sptran['doc_id'] = $doc_id;
         $sptran['doc_type'] = $doc_type;
         $sptran['operation']= $operation;
         $sptran['amount']= $amount;
         $sptran['total']= $supplier->budget + $sptran['operation']* $sptran['amount'];
         $sptran['supplier_id']=$supplier_id;
         $sps = SupTransaction::create($sptran);
         $supplier->budget =$sptran['total'];
         $supplier->save();
         return $sps;
    }
    public static function removeSubTrans($suptrans_id)
    {
         ///create SupTransaction
        $suptrans = SupTransaction::find($suptrans_id);
        if($suptrans)
        {
            $supplier = \App\Models\User::where('id',$suptrans->supplier_id)->first();
            $total = $supplier->budget - $suptrans->operation*$suptrans->amount ;
            $supplier->budget =$total;
            $supplier->save();
            $suptrans->delete();
        }
         
        return true;
    }
}
