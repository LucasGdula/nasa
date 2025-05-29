<?php

namespace Database\Factories;

use App\Models\NearEarthObject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NearEarthObject>
 */
class NearEarthObjectFactory extends Factory
{
    protected $model = NearEarthObject::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ref_id' => $this->faker->unique()->randomNumber(8),
            'name' => $this->faker->name,
            'date' => $this->faker->date(),
            'estimated_diameter_min' => $this->faker->randomFloat(4, 0, 1000),
            'estimated_diameter_max' => $this->faker->randomFloat(4, 0, 1000),
            'is_hazardous' => $this->faker->boolean,
            'abs_magnitude' => $this->faker->randomFloat(2, 10, 30),
            'velocity' => $this->faker->randomFloat(2, 0, 100),
            'miss_distance' => $this->faker->randomFloat(2, 0, 1000000),
        ];
    }
}
