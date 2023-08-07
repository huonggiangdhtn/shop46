<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    protected $fillable = ['content', 'user_id'];
    public static function insertLog($content, $user_id){
        $data['content'] = $content;
        $data['user_id'] = $user_id;
        Log::create($data);
        // DB::insert('insert into logs (content,user_id) values (?, ?)', [$content, $user_id]);
    }
}
