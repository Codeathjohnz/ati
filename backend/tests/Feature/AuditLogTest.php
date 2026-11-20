<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_list_audit_logs_with_a_nested_meta_block(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $actor = User::factory()->create(['role' => User::ROLE_PROJECT_OFFICER]);

        ActivityLog::record($actor->id, 'login', 'User', $actor->id, "{$actor->name} logged in.");
        ActivityLog::record($actor->id, 'trainee.created', 'TraineeProfile', 1, 'Created a trainee.');

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/audit-logs');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 1)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.action', 'trainee.created')
            ->assertJsonPath('data.0.user.name', $actor->name);
    }

    public function test_filtering_by_action_substring_narrows_the_results(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $actor = User::factory()->create();

        ActivityLog::record($actor->id, 'login', 'User', $actor->id, 'Logged in.');
        ActivityLog::record($actor->id, 'trainee.created', 'TraineeProfile', 1, 'Created a trainee.');

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/audit-logs?action=trainee');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.action', 'trainee.created');
    }

    public function test_a_project_officer_cannot_view_audit_logs(): void
    {
        $officer = User::factory()->create(['role' => User::ROLE_PROJECT_OFFICER]);

        $this->actingAs($officer)->getJson('/api/v1/admin/audit-logs')->assertStatus(403);
    }
}
