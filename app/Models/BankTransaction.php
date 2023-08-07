<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['total','bank_id','operation','doc_id' ,'doc_type','user_id'];
    public static function insertBankTrans($user_id,$bank_id,$operation,$doc_id,$doc_type,$total)
    {
        $bank = \App\Models\Bankaccount::where('id',$bank_id)->first();
        $bt['user_id'] =$user_id;
        $bt['bank_id'] = $bank_id;
        $bt['operation'] =$operation;
        $bt['doc_id'] =$doc_id;
        $bt['doc_type'] = $doc_type;
        $bt['total'] = $total;
        $bts = BankTransaction::create($bt);
        $bank->total += $bt['operation'] * $bt['total'];
        $bank->save();
        return $bts;
    }
    public static function removeBankTrans( $bank_trans )
    {
        $bank = \App\Models\Bankaccount::where('id',$bank_trans->bank_id)->first();
        $bank->total -= $bank_trans->operation  * $bank_trans->total ;
        $bank->save();
        $bank_trans->delete();
        return true;
    }
}
