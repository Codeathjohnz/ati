<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorageMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_read_live_filesystem_usage(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/storage');

        $response->assertOk();
        $body = $response->json();

        $this->assertArrayHasKey('filesystem', $body);
        $this->assertGreaterThan(0, $body['filesystem']['total_bytes']);
        $this->assertNotNull($body['filesystem']['used_percent']);

        $this->assertNotEmpty($body['top_consumers']);
        // vendor/ always exists and always has real content in a working install.
        $vendor = collect($body['top_consumers'])->firstWhere('path', 'vendor');
        $this->assertNotNull($vendor);
        $this->assertGreaterThan(0, $vendor['size_bytes']);

        // Consumers are sorted largest first.
        $sizes = collect($body['top_consumers'])->pluck('size_bytes')->all();
        $this->assertSame($sizes, collect($sizes)->sortDesc()->values()->all());
    }

    public function test_a_project_officer_cannot_read_storage_usage(): void
    {
        $officer = User::factory()->projectOfficer()->create();

        $this->actingAs($officer, 'sanctum')->getJson('/api/v1/admin/storage')->assertStatus(403);
    }
}
