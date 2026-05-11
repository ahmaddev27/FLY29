<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'super@29fly.local'],
            [
                'role'      => 'super_admin',
                'password'  => Hash::make('ChangeMe!Now123'),
                'full_name' => 'سوبر أدمن',
                'phone'     => null,
                'status'    => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@29fly.local'],
            [
                'role'      => 'admin',
                'password'  => Hash::make('ChangeMe!Now123'),
                'full_name' => 'مدير النظام',
                'phone'     => null,
                'status'    => 'active',
            ]
        );

        $this->command->info('Default admins created (password: ChangeMe!Now123) — ⚠ change immediately on first login.');
    }
}
