<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Show;
use App\Models\User;
use App\Permissions\ShowPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving a paginated list of shows.
     */
    public function test_list_shows(): void
    {
        $testShows = Show::factory()->count(10)->create();

        Sanctum::actingAs(User::factory()->create()->givePermissionTo([
            ShowPermissions::CAN_VIEW_SHOWS,
        ]));

        $response = $this->getJson('/api/v1/shows');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'body',
                    'start_date',
                    'end_date',
                    'is_live',
                    'enabled',
                    'moderators',
                    'locked_by',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => [
                '*' => [
                    'url',
                    'label',
                    'active',
                ],
            ],
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total',
        ]);

        $response->assertJsonFragment([
            'id' => $testShows[0]->id,
            'title' => $testShows[0]->title,
        ]);
    }

    /**
     * Test retrieving a paginated list of shows with sorting.
     */
    public function test_list_shows_with_sorting(): void
    {
        $testShows = Show::factory()->count(10)->create();

        Sanctum::actingAs(User::factory()->create()->givePermissionTo([
            ShowPermissions::CAN_VIEW_SHOWS,
        ]));

        $response = $this->getJson('/api/v1/shows?sort=id:desc&per_page=5');

        $response->assertStatus(200);

        $testShows = $testShows->sortByDesc('id')->values()->take(5);

        $response->assertJsonFragment([
            'id' => $testShows[0]->id,
            'title' => $testShows[0]->title,
        ]);
    }

    /**
     * Test retrieving a paginated list of shows with filters.
     */
    public function test_list_shows_with_filters(): void
    {
        Show::factory()->count(5)->create(['enabled' => true]);
        Show::factory()->count(3)->create(['enabled' => false]);

        Sanctum::actingAs(User::factory()->create()->givePermissionTo([
            ShowPermissions::CAN_VIEW_SHOWS,
        ]));

        $response = $this->getJson('/api/v1/shows?enabled=1');

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('total'));
    }

    /**
     * Test storing a newly created show.
     */
    public function test_store_show(): void
    {
        $data = [
            'title' => 'Test Show',
            'body' => 'This is a test show.',
            'start_date' => '2025-11-01 10:00:00',
            'end_date' => '2025-11-01 12:00:00',
            'is_live' => false,
            'enabled' => true,
        ];

        Sanctum::actingAs(User::factory()->create()->givePermissionTo([
            ShowPermissions::CAN_CREATE_SHOWS,
        ]));

        $response = $this->postJson('/api/v1/shows', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('shows', [
            'title' => $data['title'],
            'body' => $data['body'],
        ]);

        $response->assertJsonFragment([
            'title' => $data['title'],
            'body' => $data['body'],
        ]);
    }

    /**
     * Test storing a show with validation errors.
     */
    public function test_store_show_validation_error(): void
    {
        Sanctum::actingAs(User::factory()->create()->givePermissionTo([
            ShowPermissions::CAN_CREATE_SHOWS,
        ]));

        $response = $this->postJson('/api/v1/shows', []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['title']);
    }

    /**
     * Test displaying a specific show.
     */
    public function test_show_single_show(): void
    {
        $show = Show::factory()->create();

        Sanctum::actingAs(User::factory()->create()->givePermissionTo([
            ShowPermissions::CAN_VIEW_SHOWS,
        ]));

        $response = $this->getJson('/api/v1/shows/'.$show->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'id' => $show->id,
                'title' => $show->title,
                'body' => $show->body,
            ]);
    }

    /**
     * Test updating a show.
     */
    public function test_update_show(): void
    {
        $show = Show::factory()->create();

        $updateData = [
            'title' => 'Updated Show Title',
            'body' => 'Updated body content',
        ];

        Sanctum::actingAs(User::factory()->create()->givePermissionTo([
            ShowPermissions::CAN_UPDATE_SHOWS,
        ]));

        $response = $this->putJson('/api/v1/shows/'.$show->id, $updateData);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('shows', [
            'id' => $show->id,
            'title' => $updateData['title'],
            'body' => $updateData['body'],
        ]);

        $response->assertJsonFragment([
            'title' => $updateData['title'],
            'body' => $updateData['body'],
        ]);
    }

    /**
     * Test deleting a specific show.
     */
    public function test_destroy_show(): void
    {
        $show = Show::factory()->create();

        Sanctum::actingAs(User::factory()->create()->givePermissionTo([
            ShowPermissions::CAN_DELETE_SHOWS,
        ]));

        $response = $this->delete('/api/v1/shows/'.$show->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('shows', ['id' => $show->id]);
    }

    /**
     * Test accessing shows without permission.
     */
    public function test_list_shows_without_permission(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/v1/shows');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test creating a show without permission.
     */
    public function test_store_show_without_permission(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/shows', [
            'title' => 'Test Show',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
