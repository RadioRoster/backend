<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Permissions\UsersPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\UserController
 */
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the index method list users with minimal permission.
     *
     * @group UserController.Index
     * @covers ::index
     */
    public function test_index_users_with_minimal_permission(): void
    {
        // Create some dummy users
        User::factory()->count(10)->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                UsersPermissions::CAN_LIST_USERS
            ])
        );

        // Send a GET request to the index endpoint
        $response = $this->getJson('/api/v1/users');

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the response contains the paginated users
        $response->assertJsonFragment(User::paginate(15)->toArray());
    }

    /**
     * Test that the index method don't list users with unauthorized user permissions.
     *
     * @group UserController.Index
     * @covers ::index
     */
    public function test_not_index_users_with_unauthorized_user_permissions(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                UsersPermissions::CAN_SHOW_USERS,
                UsersPermissions::CAN_CREATE_USERS,
                UsersPermissions::CAN_UPDATE_USERS,
                UsersPermissions::CAN_UPDATE_USERS_SELF,
                UsersPermissions::CAN_DELETE_USERS,
            ])
        );

        // Send a GET request to the index endpoint
        $response = $this->getJson('/api/v1/users');

        // Assert that the response has a unauthorized status code
        $response->assertStatus(403);

        // Assert that the response contains the error message
        $response->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the index method without permission.
     *
     * @group UserController.Index
     * @covers ::index
     */
    public function test_not_index_users_without_any_permission(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        // Send a GET request to the index endpoint
        $response = $this->getJson('/api/v1/users');

        // Assert that the response has a unauthorized status code
        $response->assertStatus(403);

        // Assert that the response contains the error message
        $response->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test that the store method creates users with minimal permission.
     *
     * @group UserController.Store
     * @covers ::store
     */
    public function test_store_user_with_minimal_permission(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                UsersPermissions::CAN_CREATE_USERS,
            ])
        );

        // Array of user data to create
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        // Send a POST request to the store endpoint
        $response = $this->postJson('/api/v1/users', $userData);

        // Assert that the response has a created status code
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
     * Test the store method with unauthorized permission.
     *
     * @group UserController.Store
     * @covers ::store
     */

    public function test_not_store_user_with_unauthorized_user_permissions(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                UsersPermissions::CAN_LIST_USERS,
                UsersPermissions::CAN_SHOW_USERS,
                UsersPermissions::CAN_UPDATE_USERS,
                UsersPermissions::CAN_UPDATE_USERS_SELF,
                UsersPermissions::CAN_DELETE_USERS,
            ])
        );

        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        // Send a POST request to the store endpoint
        $response = $this->postJson('/api/v1/users', $userData);

        // Assert that the response has a unauthorized status code
        $response->assertStatus(403);

        // Assert that the database did not create the user
        $this->assertDatabaseMissing('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // Assert that the response contains the error message
        $response->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the store method without permission.
     *
     * @group UserController.Store
     * @covers ::store
     */
    public function test_not_store_user_without_any_permission(): void
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
        $response = $this->postJson('/api/v1/users', $userData);

        // Assert that the response has a unauthorized status code
        $response->assertStatus(403);

        // Assert that the database did not create the user
        $this->assertDatabaseMissing('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // Assert that the response contains the error message
        $response->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the show method for other users
     *
     * @group UserController.Show
     * @covers ::show
     */
    public function test_show_other_users_with_list_permission(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                UsersPermissions::CAN_LIST_USERS,
            ])
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
     * Test the show method for self.
     *
     * @group UserController.Show
     * @covers ::show
     */
    public function test_show_users_self_with_show_permission(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user->givePermissionTo([
                UsersPermissions::CAN_SHOW_USERS,
        ]));

        $user = User::find($user->id);

        // Send a GET request to the show endpoint
        $response = $this->getJson('/api/v1/users/' . $user->id);

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the response contains the user data
        $response->assertJsonFragment($user->toArray());
    }


    /**
     * Test the show method for other users with self permission.
     *
     * @group UserController.Show
     * @covers ::show
     */
    public function test_not_show_other_users_with_self_permission(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                UsersPermissions::CAN_SHOW_USERS,
            ])
        );

        $user = User::factory()->create();

        // Send a GET request to the show endpoint
        $response = $this->get('/api/v1/users/' . $user->id);

        // Assert that the response has a successful status code
        $response->assertStatus(403);

        // Assert that the response contains the error message
        $response->assertJsonFragment(['message' => 'You can only view your own user.']);
    }

    /**
     * Test the show method with unauthorized user permissions.
     *
     * @group UserController.Show
     * @covers ::show
     */
    public function test_not_show_other_users_with_unauthorized_user_permissions(): void
    {
        $unauthorizedPerms = [
            UsersPermissions::CAN_CREATE_USERS,
            UsersPermissions::CAN_UPDATE_USERS,
            UsersPermissions::CAN_UPDATE_USERS_SELF,
            UsersPermissions::CAN_DELETE_USERS,
        ];

        foreach ($unauthorizedPerms as $perm) {
            Sanctum::actingAs(
                User::factory()->create()->givePermissionTo([
                    $perm,
                ])
            );

            $user = User::factory()->create();

            // Send a GET request to the show endpoint
            $response = $this->getJson('/api/v1/users/' . $user->id);

            // Assert that the response has a unauthorized status code
            $response->assertStatus(403);

            // Assert that the response contains the error message
            $response->assertJsonFragment(['message' => 'User does not have the right permissions.']);
        }
    }

    /**
     * Test the show method without permission.
     *
     * @group UserController.Show
     * @covers ::show
     */
    public function test_not_show_users_without_any_permission(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $user = User::factory()->create();

        // Send a GET request to the show endpoint
        $response = $this->getJson('/api/v1/users/' . $user->id);

        // Assert that the response has a unauthorized status code
        $response->assertStatus(403);

        // Assert that the response contains the error message
        $response->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the update method for self.
     *
     * @group UserController.Update
     * @covers ::update
     */
    public function test_update_users_self_with_self_permission(): void
    {
        $user = User::factory()->create()->givePermissionTo([
                UsersPermissions::CAN_UPDATE_USERS_SELF,
        ]);

        Sanctum::actingAs($user);

        $userData = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ];

        // Send a PUT request to the update endpoint
        $response = $this->putJson('/api/v1/users/' . $user->id, $userData);

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // Assert that the response contains the updated user data
        $response->assertJsonFragment($userData);
    }

    /**
     * Test the update method for other users with self permission.
     *
     * @group UserController.Update
     * @covers ::update
     */
    public function test_update_other_users_with_update_permission(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                UsersPermissions::CAN_UPDATE_USERS,
            ])
        );

        $user = User::factory()->create();

        $userData = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ];

        // Send a PATCH request to the update endpoint
        $response = $this->putJson('/api/v1/users/' . $user->id, $userData);

        // Assert that the response has a unauthorized status code
        $response->assertStatus(200);

        // Assert that the database did not update the user
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // Assert that the response contains the error message
        $response->assertJsonFragment($userData);
    }

    /**
     * Test the update method for other users with self permission.
     *
     * @group UserController.Update
     * @covers ::update
     */
    public function test_not_update_other_users_with_self_permission(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                UsersPermissions::CAN_UPDATE_USERS_SELF
            ])
        );

        $user = User::factory()->create();

        $userData = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ];

        // Send a PATCH request to the update endpoint
        $response = $this->putJson('/api/v1/users/' . $user->id, $userData);

        // Assert that the response has a unauthorized status code
        $response->assertStatus(403);

        // Assert that the database did not update the user
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // Assert that the response contains the error message
        $response->assertJsonFragment(['message' => 'You can only update your own user.']);
    }

    /**
     * Test the update method for other users with unauthorized user permissions.
     */
    public function test_not_update_other_users_with_unauthorized_user_permissions(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                UsersPermissions::CAN_LIST_USERS,
                UsersPermissions::CAN_SHOW_USERS,
                UsersPermissions::CAN_CREATE_USERS,
                UsersPermissions::CAN_DELETE_USERS,
            ])
        );

        $user = User::factory()->create();

        $userData = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ];

        // Send a PATCH request to the update endpoint
        $response = $this->putJson('/api/v1/users/' . $user->id, $userData);

        // Assert that the response has a unauthorized status code
        $response->assertStatus(403);

        // Assert that the database did not update the user
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // Assert that the response contains the error message
        $response->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the update method without any permission.
     *
     * @group UserController.Update
     * @covers ::update
     */
    public function test_not_update_users_without_any_permission(): void
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
        $response = $this->putJson('/api/v1/users/' . $user->id, $userData);

        // Assert that the response has a unauthorized status code
        $response->assertStatus(403);

        // Assert that the database did not update the user
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // Assert that the response contains the error message
        $response->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the destroy method with minimal permission.
     *
     * @group UserController.Destroy
     * @covers ::destroy
     */
    public function test_destroy_users_with_minimal_permission(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                UsersPermissions::CAN_DELETE_USERS,
            ])
        );

        $user = User::factory()->create();

        // Send a DELETE request to the destroy endpoint
        $response = $this->delete('/api/v1/users/' . $user->id);

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the response contains the success message
        $response->assertJsonFragment(['data' => 'User deleted successfully.']);
    }

    /**
     * Test the destroy method with unauthorized user permissions.
     *
     * @group UserController.Destroy
     * @covers ::destroy
     */
    public function test_not_destroy_users_with_unauthorized_user_permissions(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                UsersPermissions::CAN_LIST_USERS,
                UsersPermissions::CAN_SHOW_USERS,
                UsersPermissions::CAN_CREATE_USERS,
                UsersPermissions::CAN_UPDATE_USERS,
                UsersPermissions::CAN_UPDATE_USERS_SELF,
            ])
        );

        $user = User::factory()->create();

        // Send a DELETE request to the destroy endpoint
        $response = $this->deleteJson('/api/v1/users/' . $user->id);

        // Assert that the response has a successful status code
        $response->assertStatus(403);

        // Assert that the response contains the error message
        $response->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }


    /**
     * Test the destroy method without permission.
     *
     * @group UserController.Destroy
     * @covers ::destroy
     */
    public function test_users_destroy_without_any_permission(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $user = User::factory()->create();

        // Send a DELETE request to the destroy endpoint
        $response = $this->deleteJson('/api/v1/users/' . $user->id);

        // Assert that the response has a successful status code
        $response->assertStatus(403);

        // Assert that the response contains the error message
        $response->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }
}
