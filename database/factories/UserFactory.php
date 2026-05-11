<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'role'              => 'agent',
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'full_name'         => fake()->name(),
            'phone'             => fake()->phoneNumber(),
            'status'            => 'active',
        ];
    }

    public function admin(): static            { return $this->state(['role' => 'admin']); }
    public function superAdmin(): static       { return $this->state(['role' => 'super_admin']); }
    public function accountManager(): static   { return $this->state(['role' => 'account_manager']); }
    public function suspended(): static        { return $this->state(['status' => 'suspended']); }
    public function deleted(): static          { return $this->state(['status' => 'deleted']); }
}
