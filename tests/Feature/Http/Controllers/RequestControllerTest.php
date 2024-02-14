<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Request;
use App\Models\User;
use App\Permissions\RequestPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\RequestController
 */
class RequestControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test retrieving a paginated list of requests.
     *
     * @covers ::index
     */
    public function test_list_requests(): void
    {
        $testReq = Request::factory()->count(10)->create();

        Sanctum::actingAs(User::factory()->create()->givePermissionTo([
            RequestPermissions::CAN_VIEW_REQUESTS,
        ])
        );

        $response = $this->getJson('/api/v1/requests');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'message',
                    'created_at',
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
            'id' => $testReq[0]->id,
            'name' => $testReq[0]->name,
            'message' => $testReq[0]->message,
            'created_at' => $testReq[0]->created_at,
        ]);
    }

    /**
     * Test retrieving a paginated list of requests with sorting.
     *
     * @covers ::index
     */
    public function test_list_requests_with_sorting(): void
    {
        $testReq = Request::factory()->count(10)->create();

        Sanctum::actingAs(User::factory()->create()->givePermissionTo([
            RequestPermissions::CAN_VIEW_REQUESTS,
        ])
        );

        $response = $this->getJson('/api/v1/requests?sort=id:desc&per_page=5');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'message',
                    'created_at',
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

        $testReq = $testReq->sortByDesc('id')->values()->take(5);

        $response->assertJsonFragment([
            'id' => $testReq[0]->id,
            'name' => $testReq[0]->name,
            'message' => $testReq[0]->message,
            'created_at' => $testReq[0]->created_at,
        ]);
    }

    /**
     * Test storing a newly created request.
     *
     * @covers ::store
     */
    public function test_store_request(): void
    {
        $data = [
            'name' => 'Test Request',
            'message' => 'This is a test request.',
        ];

        $response = $this->postJson('/api/v1/requests', $data);

        $this->assertDatabaseHas('requests', [
            'name' => $data['name'],
            'message' => $data['message'],
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $response->assertJsonFragment([
            'name' => $data['name'],
            'message' => $data['message'],
        ]);
    }

    /**
     * Test displaying a specific request.
     *
     * @covers ::show
     */
    public function test_show_single_request(): void
    {
        $request = Request::factory(5)->create();

        Sanctum::actingAs(User::factory()->create()->givePermissionTo([
            RequestPermissions::CAN_VIEW_REQUESTS,
        ])
        );

        $response = $this->getJson('/api/v1/requests/'.$request->get(1)->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment([
                'name' => $request->get(1)->name,
                'message' => $request->get(1)->message,
                'created_at' => $request->get(1)->created_at,
            ]);
    }

    /**
     * Test deleting a specific request.
     *
     * @covers ::destroy
     */
    public function test_destroy(): void
    {
        $request = Request::factory(3)->create();

        Sanctum::actingAs(User::factory()->create()->givePermissionTo([
            RequestPermissions::CAN_DELETE_REQUESTS,
        ])
        );

        $response = $this->delete('/api/v1/requests/' . $request->get(1)->id);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('requests', ['id' => $request->get(1)->id]);
    }
}
