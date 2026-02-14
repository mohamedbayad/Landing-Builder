<?php

namespace Tests\Feature;

use App\Models\Landing;
use App\Models\LandingMedia;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandingMediaTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $workspace;
    protected $landing;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create(['user_id' => $this->user->id]);
        $this->landing = Landing::factory()->create(['workspace_id' => $this->workspace->id]);

        $this->actingAs($this->user);
    }

    public function test_can_list_media()
    {
        LandingMedia::factory()->create([
            'landing_id' => $this->landing->id,
            'filename' => 'test.jpg'
        ]);

        $response = $this->get(route('landings.media.index', $this->landing));

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'test.jpg']);
    }

    public function test_can_upload_media()
    {
        $path = sys_get_temp_dir() . '/banner.gif';
        // 1x1 GIF
        file_put_contents($path, hex2bin('47494638396101000100800000ffffff00000021f90401000000002c00000000010001000002024401003b'));
        $file = new UploadedFile($path, 'banner.gif', 'image/gif', null, true);

        $response = $this->post(route('landings.media.store', $this->landing), [
            'files' => [$file]
        ]);

        $response->assertStatus(200);

        // Assert file exists
        $this->landing->refresh();
        $filename = 'banner.gif';
        $path = "landings/{$this->landing->uuid}/media/imgs/{$filename}";
        
        Storage::disk('public')->assertExists($path);

        $this->assertDatabaseHas('landing_media', [
            'landing_id' => $this->landing->id,
            'filename' => 'banner.gif'
        ]);
    }

    public function test_can_delete_media()
    {
        $media = LandingMedia::factory()->create([
            'landing_id' => $this->landing->id,
            'relative_path' => 'dummy/path.jpg'
        ]);
        
        // Mock file existence for delete? Storage::fake handles it if we create it.
        Storage::disk('public')->put('dummy/path.jpg', 'content');

        $response = $this->delete(route('landings.media.destroy', [$this->landing, $media]));

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('landing_media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing('dummy/path.jpg');
    }
}
