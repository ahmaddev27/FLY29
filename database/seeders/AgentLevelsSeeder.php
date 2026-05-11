<?php

namespace Database\Seeders;

use App\Models\AgentLevel;
use Illuminate\Database\Seeder;

class AgentLevelsSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'tier_name'             => 'bronze',
                'min_packages_monthly'  => 0,
                'points_per_package'    => 2,
                'amount_per_point'      => 400.00,
                'benefits'              => [
                    'label_ar'        => 'برونزي',
                    'color'           => '#A16207',
                    'manager'         => 'shared',
                    'priority_support' => false,
                    'annual_meeting'  => 0,
                ],
                'display_order' => 1,
            ],
            [
                'tier_name'             => 'silver',
                'min_packages_monthly'  => 10,
                'points_per_package'    => 3,
                'amount_per_point'      => 300.00,
                'benefits'              => [
                    'label_ar'        => 'فضي',
                    'color'           => '#94A3B8',
                    'manager'         => 'per_8',
                    'priority_support' => false,
                    'annual_meeting'  => 0,
                ],
                'display_order' => 2,
            ],
            [
                'tier_name'             => 'gold',
                'min_packages_monthly'  => 20,
                'points_per_package'    => 4,
                'amount_per_point'      => 250.00,
                'benefits'              => [
                    'label_ar'        => 'ذهبي',
                    'color'           => '#F59E0B',
                    'manager'         => 'per_3',
                    'priority_support' => true,
                    'annual_meeting'  => 1,
                ],
                'display_order' => 3,
            ],
            [
                'tier_name'             => 'diamond',
                'min_packages_monthly'  => 30,
                'points_per_package'    => 5,
                'amount_per_point'      => 200.00,
                'benefits'              => [
                    'label_ar'        => 'ماسي',
                    'color'           => '#3B82F6',
                    'manager'         => 'dedicated',
                    'priority_support' => true,
                    'urgent_support'  => true,
                    'annual_meeting'  => 2,
                ],
                'display_order' => 4,
            ],
        ];

        foreach ($levels as $level) {
            AgentLevel::updateOrCreate(['tier_name' => $level['tier_name']], $level);
        }
    }
}
