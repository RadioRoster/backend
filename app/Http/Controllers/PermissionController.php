<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiSuccessResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{

    /**
     * Display a paginated list of permissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function index(Request $request): \Illuminate\Pagination\LengthAwarePaginator
    {
        $request->validate([
            'sort' => 'string|in:id,id:asc,id:desc,name,name:asc,name:desc',
            'per_page' => 'integer|between:1,50',
        ]);

        $perms = Permission::paginate($request->per_page ?? 25);

        if ($request->sort) {
            // Sort can be a string like 'id' or 'name:desc'
            $sort = explode(':', $request->sort);
            $perms = Permission::orderBy($sort[0], $sort[1] ?? 'asc')->paginate($request->per_page ?? 25);
        } else {
            $perms = Permission::orderBy('id')->paginate($request->per_page ?? 25);
        }

        return $perms;
    }

    /**
         * Display the specified resource.
         *
         * @param \Spatie\Permission\Models\Permission $permission The permission to be displayed
         * @return \App\Http\Responses\ApiSuccessResponse The success response containing the permission
         */
        public function show(Permission $permission): ApiSuccessResponse
        {
            return new ApiSuccessResponse($permission);
        }
}
