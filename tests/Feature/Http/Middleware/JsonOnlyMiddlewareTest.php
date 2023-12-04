<?php

namespace Tests\Feature\Http\Middleware;

use Tests\TestCase;

class JsonOnlyMiddlewareTest extends TestCase
{
    /**
     * Test that non-JSON requests are rejected.
     */
    public function test_non_json_requests_are_rejected(): void
    {
        $response = $this->post('/api/v1/login', [], ['Content-Type' => 'application/xml', 'Accept' => 'application/xml']);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Only JSON requests are accepted'
            ]);
    }

    /**
     * Test that JSON requests are accepted.
     */
    public function test_json_requests_are_accepted(): void
    {
        $response = $this->post('/api/v1/login', [], ['Content-Type' => 'application/json', 'Accept' => 'application/json']);

        $response->assertStatus(302);
    }
}
