<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    /**
     * Send a password reset link to the user's email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Responses\ApiSuccessResponse|\App\Http\Responses\ApiErrorResponse
     */
    public function sendLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'reset_url' => ['required', 'url']
        ]);

        ResetPassword::createUrlUsing(function (User $user, string $token) use ($request) {
            return $request->reset_url . '/' . $token . '?email=' . $user->email;
        });

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? new ApiSuccessResponse('', Response::HTTP_NO_CONTENT)
            : new ApiErrorResponse('Unable to send reset link');
    }

    /**
     * Reset the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Responses\ApiSuccessResponse|\App\Http\Responses\ApiErrorResponse
     */
    public function reset(Request $request, string $token)
    {
        $request->merge(['token' => $token]);
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'min:8', 'confirmed']
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                $user->tokens()->delete();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? new ApiSuccessResponse('', Response::HTTP_NO_CONTENT)
            : new ApiErrorResponse('Unable to reset password');
    }
}
