<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Material;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KnowledgeHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_knowledge_hub()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('knowledge-hub.index'));

        $response->assertStatus(200);
        $response->assertViewIs('knowledge-hub.index');
    }

    public function test_user_can_upload_pdf_material_and_generate_summary()
    {
        Storage::fake('public');
        $user = User::factory()->create();

        // Buat fake PDF file
        $file = UploadedFile::fake()->create('kuliah_kalkulus.pdf', 500);

        $response = $this->actingAs($user)->post(route('knowledge-hub.store'), [
            'judul' => 'Kalkulus Lanjut Bab 1',
            'tipe_file' => 'pdf',
            'file_upload' => $file,
        ]);

        // Pastikan teralihkan ke show page
        $response->assertRedirect();
        
        // Cek database
        $this->assertDatabaseHas('materials', [
            'user_id' => $user->id,
            'judul' => 'Kalkulus Lanjut Bab 1',
            'tipe_file' => 'pdf',
        ]);

        $material = Material::where('judul', 'Kalkulus Lanjut Bab 1')->first();
        $this->assertNotNull($material->summary);
        $this->assertNotEmpty($material->summary);
    }

    public function test_material_detail_page_renders_summary_correctly()
    {
        $user = User::factory()->create();
        $material = Material::create([
            'user_id' => $user->id,
            'judul' => 'Kalkulus Lanjut Bab 1',
            'tipe_file' => 'pdf',
            'file_url' => '/materials/fake.pdf',
            'summary' => '### Ringkasan Kalkulus Lanjut' . str_repeat(' A', 100),
        ]);

        $response = $this->actingAs($user)->get(route('knowledge-hub.show', $material->id));

        $response->assertStatus(200);
        $response->assertSee('### Ringkasan Kalkulus Lanjut');
    }
}
