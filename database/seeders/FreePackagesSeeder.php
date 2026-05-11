<?php

namespace Database\Seeders;

use App\Models\FreePackage;
use Illuminate\Database\Seeder;

class FreePackagesSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name'            => 'باكج تايلاند (7 أيام)',
                'destination'     => 'Thailand',
                'points_required' => 1000,
                'duration_days'   => 7,
                'description'     => 'باكج سياحي شامل لتايلاند يشمل الإقامة والتنقلات الداخلية.',
                'is_active'       => true,
                'display_order'   => 1,
            ],
            [
                'name'            => 'باكج فيتنام (7 أيام)',
                'destination'     => 'Vietnam',
                'points_required' => 1000,
                'duration_days'   => 7,
                'description'     => 'باكج سياحي شامل لفيتنام يشمل الإقامة والتنقلات الداخلية.',
                'is_active'       => true,
                'display_order'   => 2,
            ],
            [
                'name'            => 'باكج روسيا (10 أيام)',
                'destination'     => 'Russia',
                'points_required' => 5000,
                'duration_days'   => 10,
                'description'     => 'باكج سياحي مميز لروسيا (موسكو + سانت بطرسبرغ).',
                'is_active'       => true,
                'display_order'   => 3,
            ],
        ];

        foreach ($packages as $pkg) {
            FreePackage::updateOrCreate(
                ['destination' => $pkg['destination'], 'duration_days' => $pkg['duration_days']],
                $pkg
            );
        }
    }
}
