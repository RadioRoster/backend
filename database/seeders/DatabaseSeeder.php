<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        //     'email_verified_at' => now(),
        //     'password' => bcrypt('test1234'),
        // ]);

        \App\Models\User::factory(30)->create();

        \App\Models\Show::factory(10)->create([
            'enabled' => true,
        ])->each(function ($show) {
            $show->moderators()->attach(\App\Models\User::all()->random()->id ?? \App\Models\User::factory()->create()->id, ['primary' => true]);

            // Add a random number of users to the show. Which are not already added.
            $show->moderators()->attach(\App\Models\User::all()->random(rand(0, 10))->pluck('id')->diff($show->moderators()->pluck('moderator_id')), ['primary' => false]);
        });
    }
}
