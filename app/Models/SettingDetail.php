<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingDetail extends Model
{
    use HasFactory;
    protected $fillable = ['company_name','address','logo','phone' ];

    
}
