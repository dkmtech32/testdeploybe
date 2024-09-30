<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\League>
 */
class LeagueFactory extends Factory
{
    public $league= League::class;
    use WithFaker;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
           'name' => fake()->name(),
            'images' => fake()->imageUrl(),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
            'end_date_register' => fake()->date(),
            'money' => fake()->numberBetween(0, 1000),
            'location' => fake()->address(),
            'status' => fake()->numberBetween(0, 2),
            'type_of_league' => fake()->numberBetween(0, 2),
            'format_of_league' => fake()->numberBetween(0, 2),
            'number_of_athletes' => fake()->numberBetween(0, 100),
            'owner_id' => fake()->numberBetween(0, 100),
            'slug' => fake()->slug(),
            'start_time' => fake()->time(),
        ];
    }
}
