<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('genders')->insert([
            ['name' => 'Kids', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ladies', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Men', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Unisex', 'status' => true, 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
