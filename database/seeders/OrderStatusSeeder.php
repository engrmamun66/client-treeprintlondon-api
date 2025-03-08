<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('order_statuses')->insert([
            ['name' => 'pending', 'description' => 'Order is pending'],
            ['name' => 'processing', 'description' => 'Order is being processed'],
            ['name' => 'shipped', 'description' => 'Order has been shipped'],
            ['name' => 'delivered', 'description' => 'Order has been delivered'],
        ]);
    }
}