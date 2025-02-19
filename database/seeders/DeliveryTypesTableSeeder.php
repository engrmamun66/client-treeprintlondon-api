<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('delivery_types')->insert([
            ['name' => 'Standard delivery', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Next Day delivery', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Same Day pickup', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
