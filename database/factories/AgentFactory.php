<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\CashWalletPoints;
use App\Models\PackageWalletPoints;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Agent>
 */
class AgentFactory extends Factory
{
    protected $model = Agent::class;

    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'external_agent_id' => 'AGT-' . fake()->unique()->numerify('######'),
            'business_name'     => fake()->company() . ' Travel',
            'license_number'    => 'LIC-' . fake()->unique()->numerify('######'),
            'country'           => fake()->randomElement(['SA', 'AE', 'KW', 'JO', 'EG']),
            'city'              => fake()->city(),
            'current_tier'      => 'bronze',
            'tier_valid_until'  => now()->addDays(30),
            'pending_amount'    => 0,
        ];
    }

    /**
     * Ensure the agent has both wallets created (use $factory->withWallets()).
     */
    public function withWallets(): static
    {
        return $this->afterCreating(function (Agent $agent) {
            CashWalletPoints::firstOrCreate(['agent_id' => $agent->id]);
            PackageWalletPoints::firstOrCreate(['agent_id' => $agent->id]);
        });
    }

    public function tier(string $tier): static
    {
        return $this->state(['current_tier' => $tier]);
    }

    public function suspended(): static
    {
        return $this->state([])->afterCreating(function (Agent $agent) {
            $agent->user->update(['status' => 'suspended']);
        });
    }
}
