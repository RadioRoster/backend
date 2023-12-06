<?php

namespace Tests\Feature\Http\Middleware;

use App\Models\User;
use Tests\TestCase;

class JsonOnlyMiddlewareTest extends TestCase
{
    /**
     * Test that non-JSON requests are rejected.
     */
    public function test_non_json_requests_are_rejected(): void
    {

        $user = User::factory()->create([
            'email' => 'valid@example.com',
            'password' => bcrypt('ValidPassword'),
        ]);

        $response = $this->post('/api/v1/login', [
            'body' => '<?xml version="1.0" encoding="UTF-8"?>
                <root>
                    <email>valid@example.com</email>
                    <password>ValidPassword</password>
                </root>'
        ], ['Content-Type' => 'text/xml']);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Only JSON requests are accepted'
            ]);

        $user->delete();
    }

    /**
     * Test that JSON requests are accepted.
     */
    public function test_json_requests_are_accepted(): void
    {
        $user = User::factory()->create([
            'email' => 'valid@example.com',
            'password' => bcrypt('ValidPassword'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'valid@example.com',
            'password' => 'ValidPassword',
        ], ['Content-Type' => 'application/json', 'Accept' => 'application/json']);

        $response->assertStatus(200);

        $user->delete();
    }
}
