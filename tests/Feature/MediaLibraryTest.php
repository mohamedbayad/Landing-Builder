<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\Landing;
use App\Models\MediaAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_empty_media_library()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('media.list'));
        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    public function test_user_can_upload_manual_media()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('test-image.jpg', 100);

        $response = $this->post(route('media.store'), [
            'file' => $file
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('media_assets', [
            'user_id' => $user->id,
            'filename' => 'test-image.jpg',
            'source' => 'manual'
        ]);
        
        // Assert file exists
        $asset = MediaAsset::first();
        Storage::disk('public')->assertExists($asset->relative_path);
    }

    public function test_grapesjs_upload_adds_to_library()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'WS']);
        $landing = Landing::create([
            'workspace_id' => $workspace->id,
            'name' => 'Landing',
            'slug' => 'landing',
            'uuid' => 'test-uuid',
            'status' => 'draft'
        ]);

        $this->actingAs($user);

        $file = UploadedFile::fake()->create('builder-image.png', 100);

        $response = $this->post(route('landings.media.store', $landing), [
            'files' => [$file]
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('media_assets', [
            'user_id' => $user->id,
            'landing_id' => $landing->id,
            'filename' => 'builder-image.png',
            'source' => 'grapesjs'
        ]);
    }

    public function test_user_cannot_see_others_media()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        MediaAsset::create([
            'user_id' => $user1->id,
            'filename' => 'user1.jpg',
            'relative_path' => 'path/1.jpg',
            'source' => 'manual',
            'size' => 100,
        ]);

        $this->actingAs($user2);
        $response = $this->get(route('media.list'));
        
        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    public function test_user_can_delete_media()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $asset = MediaAsset::create([
            'user_id' => $user->id,
            'filename' => 'delete-me.jpg',
            'relative_path' => 'users/' . $user->id . '/delete-me.jpg',
            'source' => 'manual',
            'size' => 100,
            'disk' => 'public'
        ]);

        // Mock file existence for deletion
        Storage::disk('public')->put($asset->relative_path, 'content');

        $response = $this->delete(route('media.destroy', $asset));
        $response->assertStatus(200);

        $this->assertDatabaseMissing('media_assets', ['id' => $asset->id]);
        Storage::disk('public')->assertMissing($asset->relative_path);
    }
}
