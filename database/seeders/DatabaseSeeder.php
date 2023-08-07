<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UsersTableSeeder::class);
        $this->call(UGroupSeeder::class);
        $this->call(WarehouseSeeder::class);
        \App\Models\User::factory(50)->create();
        \App\Models\Category::factory(50)->create();
        \App\Models\Brand::factory(50)->create();
        \App\Models\Product::factory(50)->create();
        \App\Models\UGroup::factory(10)->create();
        \App\Models\Warehouse::factory(10)->create();
        \App\Models\Log::factory(10)->create();
        \App\Models\BInventory::factory(10)->create();
        \App\Models\Inventory::factory(50)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
