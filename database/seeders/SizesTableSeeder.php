<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sizes')->insert([
            ['name' => 'XS', 'chest_round' => 36, 'length' => 26, 'sleeve' => 7.5, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'S', 'chest_round' => 37, 'length' => 26, 'sleeve' => 7.75, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'M', 'chest_round' => 39, 'length' => 27.5, 'sleeve' => 8.5, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'L', 'chest_round' => 40.5, 'length' => 28, 'sleeve' => 8.75, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'XL', 'chest_round' => 43, 'length' => 29, 'sleeve' => 9, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '2XL', 'chest_round' => 45, 'length' => 30, 'sleeve' => 9.25, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '3XL', 'chest_round' => 47.5, 'length' => 30.5, 'sleeve' => 9.5, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '4XL', 'chest_round' => 50, 'length' => 31, 'sleeve' => 10, 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
