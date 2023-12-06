<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test login with valid credentials.
     *
     * @return void
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
     *
     * @return void
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
}
