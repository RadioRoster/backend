<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the index method.
     */
    public function test_users_index(): void
    {
        // Create some dummy users
        User::factory()->count(10)->create();

        Sanctum::actingAs(
            User::factory()->create()
        );

        // Send a GET request to the index endpoint
        $response = $this->get('/api/v1/users');

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the response contains the paginated users
        $response->assertJsonFragment(User::paginate(15)->toArray());
    }

    /**
     * Test the store method.
     */
    public function test_create_user(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        // Send a POST request to the store endpoint
        $response = $this->post('/api/v1/users', $userData);

        // Assert that the response has a successful status code
        $response->assertStatus(201);

        // Assert that the database has the user
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // Assert that the response contains the user data
        $response->assertJsonFragment([
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
    }

    /**
     * Test the show method.
     */
    public function test_users_show(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $user = User::factory()->create();

        // Send a GET request to the show endpoint
        $response = $this->get('/api/v1/users/' . $user->id);

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the response contains the user data
        $response->assertJsonFragment($user->toArray());
    }

    /**
     * Test the update method.
     */
    public function test_users_update(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $user = User::factory()->create();

        $userData = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ];

        // Send a PATCH request to the update endpoint
        $response = $this->put('/api/v1/users/' . $user->id, $userData);

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // Assert that the response contains the updated user data
        $response->assertJsonFragment($userData);
    }

    /**
     * Test the destroy method.
     */
    public function test_users_destroy(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $user = User::factory()->create();

        // Send a DELETE request to the destroy endpoint
        $response = $this->delete('/api/v1/users/' . $user->id);

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the response contains the success message
        $response->assertJsonFragment(['data' => 'User deleted successfully.']);
    }
}
