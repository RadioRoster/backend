<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test login with valid credentials.
     */
    public function test_login_with_valid_credentials(): void
    {

        $user = User::factory()->create([
            'email' => 'valid@example.com',
            'password' => bcrypt('ValidPassword'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'valid@example.com',
            'password' => 'ValidPassword',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
                'access_token',
            ]);

        $this->assertAuthenticatedAs($user);

        $user->delete();
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'invalid@example.com',
            'password' => 'invalidpassword',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Invalid login credentials',
            ]);

        $this->assertGuest();
    }

    /**
     * Test logout.
     */
    public function test_logout(): void
    {

        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => 'User logged out successfully',
            ]);
    }

    /**
     * Test logout.
     */
    public function test_logout_without_user(): void
    {

        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => 'User logged out successfully',
            ]);
    }
}
