<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test sending a password reset link.
     */
    public function test_send_link(): void
    {
        $user = User::factory()->create();
        $email = $user->email;

        $response = $this->postJson('/api/v1/reset_password', ['email' => $email, 'reset_url' => 'http://localhost']);

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $email]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /**
     * Test sending a password reset link with invalid email.
     */
    public function test_send_link_with_invalid_email(): void
    {
        $response = $this->postJson('/api/v1/reset_password', ['email' => 'invalid@example.com', 'reset_url' => 'http://localhost']);

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'invalid@example.com']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Test resetting the user's password.
     */
    public function test_reset_password(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/reset_password/' . $token, [
            'email' => $user->email,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    /**
     * Test resetting the user's password with invalid token.
     */
    public function test_reset_password_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/reset_password/invalid_token', [
            'email' => $user->email,
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertFalse(Hash::check('newpassword', $user->fresh()->password));
    }

    /**
     * Test resetting the user's password with invalid email.
     */
    public function test_reset_password_with_invalid_email(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/reset_password/' . $token, [
            'email' => 'invalid@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Test resetting the user's password with password mismatch.
     */
    public function test_reset_password_with_password_mismatch(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/reset_password/' . $token, [
            'email' => $user->email,
            'password' => 'newpassword',
            'password_confirmation' => 'mismatchedpassword',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertFalse(Hash::check('newpassword', $user->fresh()->password));
    }
}
