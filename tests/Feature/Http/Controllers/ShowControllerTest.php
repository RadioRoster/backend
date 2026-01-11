<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\ShowController
 */
class ShowControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving a paginated list of shows.
     *
     * @covers ::index
     * @return void
     */
    public function test_index(): void
    {
        // Create some test user
        User::factory()->count(30)->create();

        // Create some test data
        $shows = Show::factory([
            'enabled' => true,
        ])->withUser()->count(10)->create();

        $today = today();
        $inAMonth = today()->addMonth();
        $perPage = 5;

        // Make a GET request to the index endpoint
        $response = $this->getJson('/api/v1/shows?start_date=' . $today . '&end_date=' . $inAMonth . '&per_page=' . $perPage);

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the response contains the correct number of shows
        $response->assertJsonCount($perPage, 'data');

        // Assert that the response contains the correct show data
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'start_date',
                    'end_date',
                    'is_live',
                    'enabled',
                    'moderators' => [
                        '*' => [
                            'id',
                            'name',
                            'primary',
                        ],
                    ],
                ],
            ],
        ]);

        $sortedShows = $shows->sortBy('start_date');

        $sortedShows->values()->all();

        // remove shows where the end or start date is not within the range
        $sortedShows = $sortedShows->filter(function ($show) use ($today, $inAMonth) {
            return $show->start_date->isBetween($today, $inAMonth) || $show->end_date->isBetween($today, $inAMonth);
        });


        // Assert that the response contains the correct show data
        $response->assertJsonFragment([
            'id' => $sortedShows->first()->id,
            'title' => $sortedShows->first()->title,
            'start_date' => $sortedShows->first()->start_date,
            'end_date' => $sortedShows->first()->end_date,
            'is_live' => $sortedShows->first()->is_live,
            'enabled' => $sortedShows->first()->enabled,
        ]);
    }
}
