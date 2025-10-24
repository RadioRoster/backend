<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiSuccessResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    /**
     * Handle user login.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required'],
        ]);

        if (! auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid login credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
        ], Response::HTTP_OK);
    }

    /**
     * Logout the user.
     *
     * @return \App\Http\Responses\ApiSuccessResponse
     */
    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return new ApiSuccessResponse('User logged out successfully');
    }
}
