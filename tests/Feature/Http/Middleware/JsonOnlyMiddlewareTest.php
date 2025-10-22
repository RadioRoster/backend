<?php

namespace Tests\Feature\Http\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JsonOnlyMiddlewareTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test that non-JSON requests are rejected.
     */
    public function test_non_json_requests_are_rejected(): void
    {

        $user = User::factory()->create([
            'email' => 'valid@example.com',
            'password' => bcrypt('ValidPassword'),
        ]);

        $response = $this->call(
            'POST',
            '/api/v1/login',
            server: $this->transformHeadersToServerVars([
                'Accept' => 'text/xml, application/xml, text/plain',
                'Content-Type' => 'text/xml, application/xml, text/plain',
            ]),
            content: '<?xml version="1.0" encoding="UTF-8"?>
                <root>
                    <email>valid@example.com</email>
                    <password>ValidPassword</password>
                </root>'
        );

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

    /**
     * Test that empty requests are accepted.
     */
    public function test_empty_requests_are_accepted(): void
    {

        $response = $this->post(uri: '/api/v1/login', headers: ['Accept' => 'application/json']);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The email field is required. (and 1 more error)',
                'errors' => [
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.']
                ]
            ]);

    }
}
