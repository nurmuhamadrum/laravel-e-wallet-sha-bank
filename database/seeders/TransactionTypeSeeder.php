<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('transaction_types')->insert([
            [
                'name' => 'Top Up',
                'code' => 'top_up',
                'action' => 'dr',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Withdraw',
                'code' => 'withdraw',
                'action' => 'dr',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Purchase Data Plan',
                'code' => 'purchase_data_plan',
                'action' => 'cr',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Purchase Operator Card',
                'code' => 'purchase_operator_card',
                'action' => 'cr',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
