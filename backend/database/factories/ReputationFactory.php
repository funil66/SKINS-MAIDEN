<?php

namespace Database\Factories;

use App\Models\Reputation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReputationFactory extends Factory
{
    protected $model = Reputation::class;

    public function definition()
    {
        $user = User::factory()->create();
        return [
            'user_id' => $user->id,
            'total_reviews' => $this->faker->numberBetween(0, 100),
            'average_score' => $this->faker->randomFloat(2, 1, 5),
            'meta' => ['test' => true],
        ];
    }
}
