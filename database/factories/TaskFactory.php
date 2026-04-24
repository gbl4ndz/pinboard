<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id'  => \App\Models\Project::factory(),
            'title'       => $this->faker->sentence(4, false),
            'description' => $this->faker->optional()->paragraph(),
            'created_by'  => \App\Models\User::factory(),
            'assigned_to' => null,
            'status'      => $this->faker->randomElement(\App\Enums\TaskStatus::cases())->value,
            'priority'    => $this->faker->randomElement(\App\Enums\TaskPriority::cases())->value,
            'due_date'    => $this->faker->optional()->dateTimeBetween('now', '+3 months')?->format('Y-m-d'),
            'is_public'   => $this->faker->boolean(25),
            'sort_order'  => $this->faker->numberBetween(1, 10) * 100,
        ];
    }
}
