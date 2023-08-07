<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeTransaction extends Model
{
    use HasFactory;
    protected $fillable = ['total','bank_id','operation','content','user_id'];
    public static function addFreeTrans($total,$bank_id,$operation,$content,$user_id)
    {
        $ft['total'] = $total;
        $ft['bank_id'] =  $bank_id;
        $ft['operation'] =  $operation;
        $ft['content'] =  $content;
        $ft['user_id'] =  $user_id;
        $fts=FreeTransaction::create($ft);
        return $fts;
    }
}
