<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
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
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'due_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'status' => fake()->randomElement(TaskStatus::cases()),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'user_id' => \App\Models\User::factory(),
        ];
    }


    /**
     * Indicate that the task should be assigned to a user.
     *
     * @return static
     */
    public function withAssignees(int $count = 1): static
    {
        return $this->afterCreating(function ($task) use ($count) {
            $task->assignees()->attach(
                \App\Models\User::factory($count)->create()
            );
        });
    }
}
