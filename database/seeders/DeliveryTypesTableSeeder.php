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
            ['name' => 'Standard delivery',  'cost' => 5.49, 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Next Day delivery', 'cost' => 10.49, 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Same Day pickup', 'cost' => 0.00, 'status' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
