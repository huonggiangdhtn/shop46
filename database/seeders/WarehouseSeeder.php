<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('warehouses')->insert([
            [   
               
                'title'=>"cửa hàng",
                'status'=>'active',

            ],
        ]);
        DB::table('bankaccounts')->insert([
            [   
               
                'title'=>"tiền mặt",
                'total'=>90000000,
                'status'=>'active',

            ],
        ]);
        DB::table('setting_details')->insert([
            [   
                'company_name'=>"TanphatAD computer",
                'phone'=>'0949579078 - 0500363732',
                'address'=>'02/13 Ywang Buôn Ma Thuột, Đăk Lăk',
            ],
        ]);
 
    }
}
