<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coffe>
 */
class CoffeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'size' => $this->faker->randomElement(['small', 'medium', 'large']),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'image' => 'images/default.png',
        ];
    }
}
