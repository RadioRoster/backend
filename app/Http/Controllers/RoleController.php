<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiSuccessResponse;
use App\Permissions\RolesPermissions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Role;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class RoleController extends Controller implements HasMiddleware
{
    /**
     * RoleController constructor.
     *
     * @codeCoverageIgnore
     */
    public static function middleware(): array
    {
        /**
         * Permissions:
         * - index: show-roles
         * - store: create-roles
         * - show: show-roles
         * - update: update-roles
         * - destroy: delete-roles
         */
        return [
            new Middleware(PermissionMiddleware::using(RolesPermissions::CAN_SHOW_ROLES), only: ['index', 'show']),
            new Middleware(PermissionMiddleware::using(RolesPermissions::CAN_CREATE_ROLES), only: ['store']),
            new Middleware(PermissionMiddleware::using(RolesPermissions::CAN_UPDATE_ROLES), only: ['update']),
            new Middleware(PermissionMiddleware::using(RolesPermissions::CAN_DELETE_ROLES), only: ['destroy']),
        ];
    }

    /**
     * Retrieve all roles with their permissions.
     *
     * @return \App\Http\Responses\ApiSuccessResponse
     */
    public function index()
    {
        $roles = Role::all();

        // Combine the roles with their permissions with out pivot table.
        foreach ($roles as &$role) {
            $role['permissions'] = $role->permissions()->get(['id', 'name'])->makeHidden(['pivot'])->toArray();
        }

        return new ApiSuccessResponse($roles->toArray());
    }

    /**
     * Store a newly created role in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Responses\ApiSuccessResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'present|array',
            'permissions.*' => 'sometimes|int|exists:permissions,id',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        $role->syncPermissions($validated['permissions']);

        $role['permissions'] = $role->permissions()->get(['id', 'name'])->makeHidden(['pivot'])->toArray();

        return new ApiSuccessResponse($role->toArray(), Response::HTTP_CREATED);
    }

    /**
     * Display the specified role.
     *
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \App\Http\Responses\ApiSuccessResponse
     */
    public function show(Role $role)
    {
        $role['permissions'] = $role->permissions()->get(['id', 'name'])->makeHidden(['pivot'])->toArray();
        return new ApiSuccessResponse($role);
    }

    /**
     * Update a role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Spatie\Permission\Models\Role  $role
     * @return \App\Http\Responses\ApiSuccessResponse
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,'.$role->id,
            'permissions' => 'present|array',
            'permissions.*' => 'sometimes|int|exists:permissions,id',
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);

        $role['permissions'] = $role->permissions()->get(['id', 'name'])->makeHidden(['pivot'])->toArray();

        return new ApiSuccessResponse($role);
    }

    /**
     * Delete a role.
     *
     * @param   \Spatie\Permission\Models\Role $role
     * @return  \App\Http\Responses\ApiSuccessResponse
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return new ApiSuccessResponse("Role successfully deleted.");
    }
}
