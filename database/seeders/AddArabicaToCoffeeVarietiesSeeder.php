<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddArabicaToCoffeeVarietiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exists = DB::table('coffee_varieties')->where('name', 'Arabica')->exists();
        if (!$exists) {
            DB::table('coffee_varieties')->insert([
                'name' => 'Arabica',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
