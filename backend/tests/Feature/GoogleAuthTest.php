<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    private const CLIENT_ID = 'test-client-id.apps.googleusercontent.com';
    private const KID = 'test-key-1';

    private string $privateKey = '';
    private array $jwks = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.client_id' => self::CLIENT_ID]);

        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($resource, $this->privateKey);
        $details = openssl_pkey_get_details($resource);

        $this->jwks = [
            'keys' => [[
                'kty' => 'RSA',
                'kid' => self::KID,
                'use' => 'sig',
                'alg' => 'RS256',
                'n' => JWT::urlsafeB64Encode($details['rsa']['n']),
                'e' => JWT::urlsafeB64Encode($details['rsa']['e']),
            ]],
        ];

        Http::fake([
            'www.googleapis.com/oauth2/v3/certs' => Http::response($this->jwks),
        ]);
    }

    private function makeCredential(array $overrides = []): string
    {
        $payload = array_merge([
            'iss' => 'https://accounts.google.com',
            'aud' => self::CLIENT_ID,
            'email' => 'trainee@example.com',
            'email_verified' => true,
            'iat' => time(),
            'exp' => time() + 3600,
        ], $overrides);

        return JWT::encode($payload, $this->privateKey, 'RS256', self::KID);
    }

    public function test_an_existing_trainee_can_sign_in_with_a_matching_google_email(): void
    {
        $user = User::factory()->create(['email' => 'trainee@example.com', 'role' => User::ROLE_TRAINEE]);
        TraineeProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/v1/auth/google', ['credential' => $this->makeCredential()]);

        $response->assertOk()->assertJsonPath('user.email', 'trainee@example.com');
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_an_unknown_google_email_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/auth/google', [
            'credential' => $this->makeCredential(['email' => 'nobody@example.com']),
        ]);

        $response->assertStatus(404);
    }

    public function test_a_token_for_the_wrong_audience_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'trainee@example.com', 'role' => User::ROLE_TRAINEE]);
        TraineeProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/v1/auth/google', [
            'credential' => $this->makeCredential(['aud' => 'someone-elses-client-id']),
        ]);

        $response->assertStatus(401);
    }

    public function test_a_non_trainee_account_with_the_same_email_cannot_sign_in_with_google(): void
    {
        User::factory()->create(['email' => 'trainee@example.com', 'role' => User::ROLE_PROJECT_OFFICER]);

        $response = $this->postJson('/api/v1/auth/google', ['credential' => $this->makeCredential()]);

        $response->assertStatus(404);
    }

    public function test_a_deactivated_trainee_cannot_sign_in_with_google(): void
    {
        $user = User::factory()->create(['email' => 'trainee@example.com', 'role' => User::ROLE_TRAINEE, 'is_active' => false]);
        TraineeProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/v1/auth/google', ['credential' => $this->makeCredential()]);

        $response->assertStatus(403);
    }
}
