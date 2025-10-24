<?php

namespace Tests\Unit\Services;

use App\Models\Show;
use App\Models\User;
use App\Services\ShowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ShowService $showService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->showService = new ShowService();
    }

    /**
     * Test getting paginated shows.
     */
    public function test_get_shows(): void
    {
        Show::factory()->count(15)->create();

        $result = $this->showService->getShows([], 10);

        $this->assertEquals(10, $result->count());
        $this->assertEquals(15, $result->total());
    }

    /**
     * Test getting shows with filters.
     */
    public function test_get_shows_with_filters(): void
    {
        Show::factory()->count(5)->create(['enabled' => true]);
        Show::factory()->count(3)->create(['enabled' => false]);

        $result = $this->showService->getShows(['enabled' => true], 25);

        $this->assertEquals(5, $result->total());
    }

    /**
     * Test getting shows with sorting.
     */
    public function test_get_shows_with_sorting(): void
    {
        Show::factory()->create(['title' => 'A Show']);
        Show::factory()->create(['title' => 'Z Show']);

        $result = $this->showService->getShows(['sort' => 'title:asc'], 25);

        $this->assertEquals('A Show', $result->first()->title);
    }

    /**
     * Test getting a single show.
     */
    public function test_get_show(): void
    {
        $show = Show::factory()->create();

        $result = $this->showService->getShow($show->id);

        $this->assertNotNull($result);
        $this->assertEquals($show->id, $result->id);
        $this->assertEquals($show->title, $result->title);
    }

    /**
     * Test getting a non-existent show.
     */
    public function test_get_show_not_found(): void
    {
        $result = $this->showService->getShow(999);

        $this->assertNull($result);
    }

    /**
     * Test creating a show.
     */
    public function test_create_show(): void
    {
        $data = [
            'title' => 'New Show',
            'body' => 'Show description',
            'start_date' => '2025-11-01 10:00:00',
            'end_date' => '2025-11-01 12:00:00',
            'is_live' => false,
            'enabled' => true,
        ];

        $show = $this->showService->createShow($data);

        $this->assertNotNull($show);
        $this->assertEquals($data['title'], $show->title);
        $this->assertDatabaseHas('shows', ['title' => $data['title']]);
    }

    /**
     * Test updating a show.
     */
    public function test_update_show(): void
    {
        $show = Show::factory()->create();

        $updateData = [
            'title' => 'Updated Title',
            'body' => 'Updated body',
        ];

        $result = $this->showService->updateShow($show->id, $updateData);

        $this->assertNotNull($result);
        $this->assertEquals($updateData['title'], $result->title);
        $this->assertEquals($updateData['body'], $result->body);
        $this->assertDatabaseHas('shows', [
            'id' => $show->id,
            'title' => $updateData['title'],
        ]);
    }

    /**
     * Test updating a non-existent show.
     */
    public function test_update_show_not_found(): void
    {
        $result = $this->showService->updateShow(999, ['title' => 'Updated']);

        $this->assertNull($result);
    }

    /**
     * Test deleting a show.
     */
    public function test_delete_show(): void
    {
        $show = Show::factory()->create();

        $result = $this->showService->deleteShow($show->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('shows', ['id' => $show->id]);
    }

    /**
     * Test deleting a non-existent show.
     */
    public function test_delete_show_not_found(): void
    {
        $result = $this->showService->deleteShow(999);

        $this->assertFalse($result);
    }

    /**
     * Test toggling live status.
     */
    public function test_toggle_live_status(): void
    {
        $show = Show::factory()->create(['is_live' => false]);

        $result = $this->showService->toggleLiveStatus($show->id);

        $this->assertNotNull($result);
        $this->assertTrue($result->is_live);

        $result = $this->showService->toggleLiveStatus($show->id);

        $this->assertNotNull($result);
        $this->assertFalse($result->is_live);
    }

    /**
     * Test locking a show.
     */
    public function test_lock_show(): void
    {
        $show = Show::factory()->create();
        $user = User::factory()->create();

        $result = $this->showService->lockShow($show->id, $user->id);

        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->locked_by);
        $this->assertDatabaseHas('shows', [
            'id' => $show->id,
            'locked_by' => $user->id,
        ]);
    }

    /**
     * Test unlocking a show.
     */
    public function test_unlock_show(): void
    {
        $user = User::factory()->create();
        $show = Show::factory()->create(['locked_by' => $user->id]);

        $result = $this->showService->unlockShow($show->id);

        $this->assertNotNull($result);
        $this->assertNull($result->locked_by);
        $this->assertDatabaseHas('shows', [
            'id' => $show->id,
            'locked_by' => null,
        ]);
    }
}

