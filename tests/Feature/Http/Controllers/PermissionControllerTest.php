<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_get_paginate_permissions(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->getJson('/api/v1/permissions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'guard_name',
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
    }

    public function test_get_paginate_permissions_with_sort(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->getJson('/api/v1/permissions?sort=id:desc');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'guard_name',
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
    }

    public function test_get_paginate_permissions_with_per_page(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->getJson('/api/v1/permissions?per_page=5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'guard_name',
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
    }

    public function test_get_paginate_permissions_with_sort_and_per_page(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->getJson('/api/v1/permissions?sort=id:desc&per_page=5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'guard_name',
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
    }

    public function test_get_single_permission(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->getJson('/api/v1/permissions/1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'guard_name',
                'created_at',
                'updated_at',
            ],
            'status',
            'timestamp',
        ]);
    }
}
