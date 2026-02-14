<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Landing;
use App\Models\Workspace;
use App\Models\MediaAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MediaSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_backfill_command_indexes_existing_images()
    {
        // 1. Setup Data
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $landing = Landing::factory()->create(['workspace_id' => $workspace->id]);

        // 2. Create physical file in storage (mimic existing file)
        $path = "landings/{$landing->uuid}/assets/media/imgs/test.png";
        Storage::disk('public')->put($path, 'fake content');
        
        // Ensure directory exists for command relative path logic if using real files, 
        // but here we are using Storage::fake which might not interact with glob() in the command dependent on File facade?
        // The command uses `File::allFiles(storage_path(...))`. 
        // `Storage::fake` creates files in a temp directory, but `storage_path` points to real app storage.
        // So we need to actually write to the real storage path cleaned up later, OR mock File facade.
        // Making real files is integration testing. Let's do that but be careful to clean up.
        
        $realPath = storage_path("app/public/landings/{$landing->uuid}/assets/media/imgs");
        if (!File::exists($realPath)) {
            File::makeDirectory($realPath, 0755, true);
        }
        File::put($realPath . '/backfill-test.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+P+/HgAFhAJ/wlseKgAAAABJRU5ErkJggg=='));

        // 3. Run Command
        $this->artisan('media:backfill')
             ->assertExitCode(0);

        // 4. Verification
        $this->assertDatabaseHas('media_assets', [
            'landing_id' => $landing->id,
            'filename' => 'backfill-test.png',
            'source' => 'backfill',
        ]);

        // Cleanup
        File::deleteDirectory(storage_path("app/public/landings/{$landing->uuid}"));
    }

    public function test_grapesjs_upload_variable_input_names()
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $landing = Landing::factory()->create(['workspace_id' => $workspace->id]);

        $this->actingAs($user);

        // Test with 'files' (array)
        $file1 = UploadedFile::fake()->createWithContent('img1.png', 'fake image content');
        $response = $this->postJson(route('landings.media.store', $landing), [
            'files' => [$file1]
        ]);
        $response->assertStatus(200);
        $this->assertTrue(MediaAsset::where('filename', 'like', '%img1.png')->exists());

        // Test with 'file' (single)
        $file2 = UploadedFile::fake()->createWithContent('img2.png', 'fake image content');
        $response2 = $this->postJson(route('landings.media.store', $landing), [
            'file' => $file2
        ]);
        $response2->assertStatus(200);
        // We can't easily check filename exact match due to unique ID prefixing in controller
        // Check count
        $this->assertEquals(2, MediaAsset::where('landing_id', $landing->id)->count());
    }
}
