<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'author_id'   => User::factory(),
            'assignee_id' => null,
            'title'       => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'status'      => 'pending',
            'due_date'    => now()->addDays(rand(1,30))->toDateString(),
        ];
    }
}
