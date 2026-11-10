<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_requesting_a_reset_link_for_a_known_email_sends_the_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'trainee@example.com']);

        $response = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'trainee@example.com']);

        $response->assertOk();
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_requesting_a_reset_link_for_an_unknown_email_gives_the_same_response(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/auth/forgot-password', ['email' => 'nobody@example.com']);

        $response->assertOk()->assertJsonPath('message', 'If that email matches an account, a password reset link has been sent.');
        Notification::assertNothingSent();
    }

    public function test_a_valid_token_resets_the_password(): void
    {
        $user = User::factory()->create(['email' => 'trainee@example.com', 'password' => Hash::make('OldPassword1')]);
        $token = Password::createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'trainee@example.com',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('NewPassword1', $user->fresh()->password));
    }

    public function test_an_invalid_token_is_rejected(): void
    {
        User::factory()->create(['email' => 'trainee@example.com']);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'not-a-real-token',
            'email' => 'trainee@example.com',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

        $response->assertStatus(422);
    }

    public function test_the_reset_email_links_into_the_frontend_app(): void
    {
        config(['app.frontend_url' => 'https://ati.aticaraga.tech']);
        $user = User::factory()->create(['email' => 'trainee@example.com']);

        $notification = new ResetPasswordNotification('sample-token');
        $mail = $notification->toMail($user);

        $this->assertStringContainsString('https://ati.aticaraga.tech/reset-password?token=sample-token', $mail->actionUrl);
    }
}
