<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StudySession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyPlannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_study_planner()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('study-planner.index'));

        $response->assertStatus(200);
        $response->assertViewIs('study-planner.index');
    }

    public function test_user_can_add_custom_study_session()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('study-planner.store'), [
            'judul' => 'Belajar Kalkulus Dasar',
            'hari' => 'Senin',
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
        ]);

        $response->assertRedirect(route('study-planner.index'));
        $this->assertDatabaseHas('study_sessions', [
            'user_id' => $user->id,
            'judul' => 'Belajar Kalkulus Dasar',
            'hari' => 'Senin',
        ]);
    }

    public function test_user_can_export_study_planner_pdf()
    {
        $user = User::factory()->create();
        
        StudySession::create([
            'user_id' => $user->id,
            'judul' => 'Belajar Kalkulus',
            'hari' => 'Senin',
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '11:00',
        ]);

        $response = $this->actingAs($user)->get(route('study-planner.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
