<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Permissions\RolesPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\Role;
use App\Permissions\UsersPermissions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\RoleController
 */
class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the index method of RoleController with a user with minimal permissions.
     *
     * @group RoleController.index
     * @covers ::index
     */
    public function test_index_roles_with_minimal_permission(): void
    {
        Role::factory()->count(3)->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_SHOW_ROLES
            ]),
        );

        $roles = Role::all();

        // Combine the roles with their permissions with out pivot table.
        foreach ($roles as &$role) {
            $role['permissions'] = $role->permissions()->get(['id', 'name'])->makeHidden(['pivot'])->toArray();
        }

        $response = $this->getJson('/api/v1/roles');

        $response->assertStatus(200);

        foreach ($roles as $role) {
            $response->assertJsonFragment($role->toArray());
        }
    }

    /**
     * Test the index method of RoleController without roles permissions.
     *
     * @group RoleController.index
     * @covers ::index
     */
    public function test_not_index_roles_without_any_permission(): void
    {
        Role::factory()->count(3)->create();

        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->getJson('/api/v1/roles');

        $response->assertStatus(403);

        $response->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the index method of RoleController with all unauthorized role permissions.
     *
     * @group RoleController.index
     * @covers ::index
     */
    public function test_not_index_roles_with_unauthorized_role_permissions(): void
    {
        Role::factory()->count(3)->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_CREATE_ROLES,
                RolesPermissions::CAN_UPDATE_ROLES,
                RolesPermissions::CAN_DELETE_ROLES,
            ]),
        );

        $response = $this->getJson('/api/v1/roles');

        $response->assertStatus(403);

        $response->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the store method of RoleController with minimal permissions.
     *
     * @group RoleController.store
     * @covers ::store
     */
    public function test_store_roles_with_minimal_permission(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_CREATE_ROLES
            ]),
        );

        $data = [
            'name' => 'Test Role',
            'permissions' => [
                Permission::findByName(UsersPermissions::CAN_SHOW_USERS, 'web')->id,
            ],
        ];

        $response = $this->postJson('/api/v1/roles', $data);

        $resData = [
            'name' => 'Test Role',
            'guard_name' => 'web',
            'permissions' => [
                [
                    'id' => Permission::findByName(UsersPermissions::CAN_SHOW_USERS, 'web')->id,
                    'name' => UsersPermissions::CAN_SHOW_USERS,
                ],
            ],
        ];

        $this->assertDatabaseHas('roles', ['name' => 'Test Role']);

        $response
            ->assertStatus(201)
            ->assertJsonFragment($resData);
    }

    /**
     * Test the store method of RoleController with wrong permission id.
     *
     * @group RoleController.store
     * @covers ::store
     */
    public function test_not_store_roles_with_wrong_permission_id(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_CREATE_ROLES
            ]),
        );

        do {
            $randPermId = rand(0, 999);
        } while (DB::table('permissions')->where('id', $randPermId)->exists());

        $data = [
            'name' => 'Test Role',
            'permissions' => [
                $randPermId,
            ],
        ];

        $response = $this->postJson('/api/v1/roles', $data);

        $this->assertDatabaseMissing('roles', ['name' => 'Test Role']);

        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'The selected permissions.0 is invalid.']);
    }

    /**
     * Test the store method of RoleController with duplicate name.
     *
     * @group RoleController.store
     * @covers ::store
     */
    public function test_not_store_roles_with_duplicate_name(): void
    {
        $roleOne = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_CREATE_ROLES
            ]),
        );

        $data = [
            'name' => $roleOne->name,
            'permissions' => [
                Permission::findByName(UsersPermissions::CAN_SHOW_USERS, 'web')->id,
            ],
        ];

        $response = $this->postJson('/api/v1/roles', $data);

        $this->assertDatabaseCount('roles', 1);

        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'The name has already been taken.']);
    }

    /**
     * Test the store method of RoleController with unauthorized role permissions.
     *
     * @group RoleController.store
     * @covers ::store
     */
    public function test_not_store_roles_with_unauthorized_role_permissions(): void
    {
        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_SHOW_ROLES,
                RolesPermissions::CAN_UPDATE_ROLES,
                RolesPermissions::CAN_DELETE_ROLES,
            ]),
        );

        $data = [
            'name' => 'Test Role',
            'permissions' => [
                Permission::findByName(UsersPermissions::CAN_SHOW_USERS, 'web')->id,
            ],
        ];

        $response = $this->postJson('/api/v1/roles', $data);

        $this->assertDatabaseMissing('roles', ['name' => 'Test Role']);

        $response
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the store method of RoleController without any permissions.
     *
     * @group RoleController.store
     * @covers ::store
     */
    public function test_not_stores_role_without_any_permission(): void
    {
        Sanctum::actingAs(
            User::factory()->create(),
        );

        $data = [
            'name' => 'Test Role',
            'permissions' => [
                Permission::findByName(UsersPermissions::CAN_SHOW_USERS, 'web')->id,
            ],
        ];

        $response = $this->postJson('/api/v1/roles', $data);

        $this->assertDatabaseMissing('roles', ['name' => 'Test Role']);

        $response
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the show method of RoleController with minimal permissions.
     *
     * @group RoleController.show
     * @covers ::show
     */
    public function test_show_roles_with_minimal_permission(): void
    {
        Role::factory()->count(3)->create();
        $role = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_SHOW_ROLES
            ]),
        );

        $response = $this->getJson('/api/v1/roles/' . $role->id);

        $role['permissions'] = $role->permissions()->get(['id', 'name'])->makeHidden(['pivot'])->toArray();

        $response
            ->assertStatus(200)
            ->assertJsonFragment($role->toArray());
    }

    /**
     * Test the show method of RoleController with wrong role id.
     *
     * @group RoleController.show
     * @covers ::show
     */
    public function test_not_show_roles_with_wrong_role_id(): void
    {
        Role::factory()->count(3)->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_SHOW_ROLES
            ]),
        );

        do {
            $randRoleId = rand(0, 999);
        } while (DB::table('roles')->where('id', $randRoleId)->exists());

        $response = $this->getJson('/api/v1/roles/' . $randRoleId);

        $response
            ->assertStatus(404)
            ->assertJsonFragment(['message' => 'No query results for model [App\\Models\\Role] ' . $randRoleId]);
    }

    /**
     * Test the show method of RoleController with unauthorized role permissions.
     *
     * @group RoleController.show
     * @covers ::show
     */
    public function test_not_show_roles_with_unauthorized_role_permissions(): void
    {
        Role::factory()->count(3)->create();
        $role = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_CREATE_ROLES,
                RolesPermissions::CAN_UPDATE_ROLES,
                RolesPermissions::CAN_DELETE_ROLES,
            ]),
        );

        $response = $this->getJson('/api/v1/roles/' . $role->id);

        $response
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the show method of RoleController without any permissions.
     *
     * @group RoleController.show
     * @covers ::show
     */
    public function test_not_show_roles_without_any_permission(): void
    {
        Role::factory()->count(3)->create();
        $role = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->getJson('/api/v1/roles/' . $role->id);

        $response
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the update method of RoleController with minimal permissions.
     *
     * @group RoleController.update
     * @covers ::update
     */
    public function test_update_roles_with_minimal_permission(): void
    {
        $role = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_UPDATE_ROLES
            ]),
        );

        $data = [
            'name' => 'Updated Role',
            'permissions' => [
                Permission::findByName(UsersPermissions::CAN_SHOW_USERS, 'web')->id,
            ],
        ];

        $response = $this->putJson('/api/v1/roles/' . $role->id, $data);



        $this->assertDatabaseHas('roles', ['id'=> $role->id, 'name' => 'Updated Role']);

        $updatedRole = Role::findById($role->id, 'web');
        $updatedRole['permissions'] = $updatedRole->permissions()->get(['id', 'name'])->makeHidden(['pivot'])->toArray();

        $response
            ->assertStatus(200)
            ->assertJsonFragment($updatedRole->toArray());
    }

    /**
     * Test the update method of RoleController without permissions array.
     *
     * @group RoleController.update
     * @covers ::update
     */
    public function test_not_update_roles_without_permissions_array(): void
    {
        $role = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_UPDATE_ROLES
            ]),
        );

        $data = [
            'name' => 'Updated Role',
        ];

        $response = $this->putJson('/api/v1/roles/' . $role->id, $data);

        $this->assertDatabaseMissing('roles', ['id'=> $role->id, 'name' => 'Updated Role']);

        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'The permissions field must be present.']);
    }

    /**
     * Test the update method of RoleController with wrong permission id.
     *
     * @group RoleController.update
     * @covers ::update
     */
    public function test_not_update_roles_with_wrong_permission_id(): void
    {
        $role = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_UPDATE_ROLES
            ]),
        );

        do {
            $randPermId = rand(0, 999);
        } while (DB::table('permissions')->where('id', $randPermId)->exists());

        $data = [
            'name' => 'Updated Role',
            'permissions' => [
                $randPermId,
            ],
        ];

        $response = $this->putJson('/api/v1/roles/' . $role->id, $data);

        $this->assertDatabaseMissing('roles', ['id'=> $role->id, 'name' => 'Updated Role']);

        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'The selected permissions.0 is invalid.']);
    }

    /**
     * Test the update method of RoleController with duplicate name.
     *
     * @group RoleController.update
     * @covers ::update
     */
    public function test_not_update_roles_with_duplicate_name(): void
    {
        $roleOne = Role::factory()->create();
        $roleTwo = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_UPDATE_ROLES
            ]),
        );

        $data = [
            'name' => $roleOne->name,
            'permissions' => [
                Permission::findByName(UsersPermissions::CAN_SHOW_USERS, 'web')->id,
            ],
        ];

        $response = $this->putJson('/api/v1/roles/' . $roleTwo->id, $data);

        $this->assertDatabaseCount('roles', 2);

        $response
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'The name has already been taken.']);
    }

    /**
     * Test the update method of RoleController with unauthorized role permissions.
     *
     * @group RoleController.update
     * @covers ::update
     */
    public function test_not_update_roles_with_unauthorized_role_permissions(): void
    {
        $role = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_SHOW_ROLES,
                RolesPermissions::CAN_CREATE_ROLES,
                RolesPermissions::CAN_DELETE_ROLES,
            ]),
        );

        $data = [
            'name' => 'Updated Role',
            'permissions' => [
                Permission::findByName(UsersPermissions::CAN_SHOW_USERS, 'web')->id,
            ],
        ];

        $response = $this->putJson('/api/v1/roles/' . $role->id, $data);

        $this->assertDatabaseMissing('roles', ['id'=> $role->id, 'name' => 'Updated Role']);

        $response
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the update method of RoleController without any permissions.
     *
     * @group RoleController.update
     * @covers ::update
     */
    public function test_not_update_roles_without_any_permission(): void
    {
        $role = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create(),
        );

        $data = [
            'name' => 'Updated Role',
            'permissions' => [
                Permission::findByName(UsersPermissions::CAN_SHOW_USERS, 'web')->id,
            ],
        ];

        $response = $this->putJson('/api/v1/roles/' . $role->id, $data);

        $this->assertDatabaseMissing('roles', ['id'=> $role->id, 'name' => 'Updated Role']);

        $response
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the destroy method of RoleController with minimal permissions.
     *
     * @group RoleController.destroy
     * @covers ::destroy
     */
    public function test_destroy_roles_with_minimal_permission(): void
    {
        $role = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_DELETE_ROLES
            ]),
        );

        $response = $this->deleteJson('/api/v1/roles/' . $role->id);

        $this->assertDatabaseMissing('roles', ['id'=> $role->id]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment(['data' => 'Role successfully deleted.']);
    }

    /**
     * Test the destroy method of RoleController with wrong role id.
     *
     * @group RoleController.destroy
     * @covers ::destroy
     */
    public function test_not_destroy_roles_with_wrong_role_id(): void
    {
        Role::factory()->count(3)->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_DELETE_ROLES
            ]),
        );

        do {
            $randRoleId = rand(0, 999);
        } while (DB::table('roles')->where('id', $randRoleId)->exists());

        $response = $this->deleteJson('/api/v1/roles/' . $randRoleId);

        $this->assertDatabaseCount('roles', 3);

        $response
            ->assertStatus(404)
            ->assertJsonFragment(['message' => 'No query results for model [App\\Models\\Role] ' . $randRoleId]);
    }

    /**
     * Test the destroy method of RoleController with unauthorized role permissions.
     *
     * @group RoleController.destroy
     * @covers ::destroy
     */
    public function test_not_destroy_roles_with_unauthorized_role_permissions(): void
    {
        $role = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create()->givePermissionTo([
                RolesPermissions::CAN_SHOW_ROLES,
                RolesPermissions::CAN_CREATE_ROLES,
                RolesPermissions::CAN_UPDATE_ROLES,
            ]),
        );

        $response = $this->deleteJson('/api/v1/roles/' . $role->id);

        $this->assertDatabaseCount('roles', 1);

        $response
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }

    /**
     * Test the destroy method of RoleController without any permissions.
     *
     * @group RoleController.destroy
     * @covers ::destroy
     */
    public function test_not_destroy_roles_without_any_permission(): void
    {
        $role = Role::factory()->create();

        Sanctum::actingAs(
            User::factory()->create(),
        );

        $response = $this->deleteJson('/api/v1/roles/' . $role->id);

        $this->assertDatabaseCount('roles', 1);

        $response
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'User does not have the right permissions.']);
    }
}
