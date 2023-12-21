<?php

namespace Database\Factories;

use App\Models\Show;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Show>
 */
class ShowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        do {

            $start_date = $this->faker->dateTimeBetween('-2 days', '+1 month');
            $end_date = $this->faker->dateTimeBetween($start_date, (clone $start_date)->modify('+48 hours'));

            $show = Show::where('start_date', '<=', $end_date)->where('end_date', '>=', $start_date)->first();
        } while ($show !== null);

        return [
            'title' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'is_live' => $this->faker->boolean(),
            'enabled' => $this->faker->boolean(),
            'locked_by' => $this->faker->randomElement([null, User::all()->random()->id ?? User::factory()->create()->id])
        ];
    }

    public function withUser()
    {
        return $this->afterCreating(function (Show $show) {
            // Add a user to the show and set it as primary.
            $show->moderators()->attach(User::all()->random()->id ?? User::factory()->create()->id, ['primary' => true]);

            // Add a random number of users to the show. Which are not already added.
            $show->moderators()->attach(User::all()->random()->pluck('id')->diff($show->moderators()->pluck('moderator_id')), ['primary' => false]);
        });
    }
}
