<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Models\User;
use App\Permissions\UsersPermissions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        /**
         * Permissions:
         * - index: list-users
         * - store: create-users
         * - show: show-users || list-users
         * - update-users || update-users-self
         * - destroy: delete-users
         */
        $this->middleware('permission:'.UsersPermissions::CAN_LIST_USERS)->only('index');
        $this->middleware('permission:'.UsersPermissions::CAN_LIST_USERS.'|'.UsersPermissions::CAN_SHOW_USERS)->only('show');
        $this->middleware('permission:'.UsersPermissions::CAN_CREATE_USERS)->only('store');
        $this->middleware('permission:'.UsersPermissions::CAN_UPDATE_USERS.'|'.UsersPermissions::CAN_UPDATE_USERS_SELF)->only('update');
        $this->middleware('permission:'.UsersPermissions::CAN_DELETE_USERS)->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::paginate(
            request()->has('size') ? request()->size : 15
        );

        return new ApiSuccessResponse($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($validated);

        return new ApiSuccessResponse($user, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        if(!$authUser->checkPermissionTo(UsersPermissions::CAN_LIST_USERS) && !$authUser->is($user)) {
            return new ApiErrorResponse("You can only view your own user.", status: Response::HTTP_FORBIDDEN);
        }
        return new ApiSuccessResponse($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'sometimes|required|min:8|confirmed',
        ]);

        /** @var User $authUser */
        $authUser = auth()->user();

        if($authUser->checkPermissionTo(UsersPermissions::CAN_UPDATE_USERS_SELF) && !$authUser->is($user)) {
            return new ApiErrorResponse("You can only update your own user.", status: Response::HTTP_FORBIDDEN);
        }

        $user->update($validated);

        return new ApiSuccessResponse($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return new ApiSuccessResponse("User deleted successfully.");
    }
}
