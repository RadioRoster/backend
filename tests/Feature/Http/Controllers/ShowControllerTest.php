<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Show;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving a paginated list of shows.
     *
     * @return void
     */
    public function testIndex(): void
    {
        // Create some test data
        $shows = Show::factory()->count(5)->create();

        // Make a GET request to the index endpoint
        $response = $this->get('/api/v1/shows');

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the response contains the correct number of shows
        $response->assertJsonCount(5, 'data');

        // Assert that the response contains the correct show data
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'start_date',
                    'end_date',
                    'live',
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
    }
}
