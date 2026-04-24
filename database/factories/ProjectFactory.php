<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'name'        => ucwords($name),
            'slug'        => \Illuminate\Support\Str::slug($name),
            'description' => $this->faker->sentence(),
            'is_public'   => $this->faker->boolean(30),
            'created_by'  => \App\Models\User::factory(),
        ];
    }
}
