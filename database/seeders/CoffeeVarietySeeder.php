<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CoffeeVariety;

class CoffeeVarietySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $varieties = [
            [
                'name' => 'Liberica',
                'color' => '#4A6741',
                'description' => 'A rare coffee species native to the Philippines, known for its distinctive smoky, woody, and floral flavor with a full body and irregular-shaped beans.',
            ],
            [
                'name' => 'Excelsa',
                'color' => '#D4AF37',
                'description' => 'Grown mostly in Southeast Asia, Excelsa produces a tart, fruity, and complex flavor profile. It is often used to add depth and character to coffee blends.',
            ],
            [
                'name' => 'Robusta',
                'color' => '#8B4513',
                'description' => 'A hardy and widely cultivated coffee species known for its strong, bitter taste and high caffeine content. Commonly used in espresso blends and instant coffee.',
            ],
            [
                'name' => 'Arabica',
                'color' => '#C0392B',
                'description' => 'The most popular coffee species worldwide, prized for its smooth, mild flavor with hints of sweetness and acidity. Grown at high altitudes for optimal quality.',
            ],
        ];

        foreach ($varieties as $variety) {
            CoffeeVariety::updateOrCreate(
                ['name' => $variety['name']],
                $variety
            );
        }
    }
}
