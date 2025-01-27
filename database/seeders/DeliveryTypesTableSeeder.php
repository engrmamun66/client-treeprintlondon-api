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
            ['name' => 'Urgent 2-3 days', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Standard 4-7 days', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
