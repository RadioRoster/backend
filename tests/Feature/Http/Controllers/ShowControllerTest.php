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

        // Sort the shows by start date and take the first 5
        $shows = $shows->sortBy('start_date')->values()->take($perPage);

        // Assert that the response contains the correct show data
        $response->assertJsonFragment([
            'id' => $shows->first()->id,
            'title' => $shows->first()->title,
            'start_date' => $shows->first()->start_date,
            'end_date' => $shows->first()->end_date,
            'is_live' => $shows->first()->is_live,
            'enabled' => $shows->first()->enabled,
        ]);
    }
}
