<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('types')->insert([
            ['name' => 'Popular Product', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Clothing', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Advertising Materials', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
