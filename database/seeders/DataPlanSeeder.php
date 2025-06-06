<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('data_plans')->insert([
            [
                'name' => 'Telkomsel 1GB',
                'operator_card_id' => 1,
                'price' => 10000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Telkomsel 2GB',
                'operator_card_id' => 1,
                'price' => 15000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Singtel 1GB',
                'operator_card_id' => 2,
                'price' => 15000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Singtel 2GB',
                'operator_card_id' => 2,
                'price' => 20000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Indosat Ooredoo 1GB',
                'operator_card_id' => 3,
                'price' => 12000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Indosat Ooredoo 3GB',
                'operator_card_id' => 3,
                'price' => 19000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
