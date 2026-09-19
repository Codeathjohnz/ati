<?php

namespace Tests\Feature;

use App\Models\TraineeProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_delete_another_account_and_keeps_the_trainee_profile(): void
    {
        $admin = User::factory()->admin()->create();
        $traineeUser = User::factory()->trainee()->create();
        $profile = TraineeProfile::factory()->create(['user_id' => $traineeUser->id]);

        $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/admin/users/{$traineeUser->id}")->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $traineeUser->id]);
        $this->assertDatabaseHas('trainee_profiles', ['id' => $profile->id, 'user_id' => null]);
    }

    public function test_an_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/admin/users/{$admin->id}")->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
