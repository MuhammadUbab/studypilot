<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Habit;
use App\Models\HabitLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class HabitTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_habit_tracker()
    {
        $response = $this->get(route('habits.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_habit_tracker()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('habits.index'));

        $response->assertStatus(200);
        $response->assertViewIs('habits.index');
    }

    public function test_user_can_create_habit()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('habits.store'), [
            'name' => 'Membaca Buku 30 Menit',
        ]);

        $response->assertRedirect(route('habits.index'));
        $this->assertDatabaseHas('habits', [
            'user_id' => $user->id,
            'name' => 'Membaca Buku 30 Menit',
            'streak' => 0,
        ]);
    }

    public function test_user_can_update_own_habit()
    {
        $user = User::factory()->create();
        $habit = Habit::create([
            'user_id' => $user->id,
            'name' => 'Belajar Coding',
            'streak' => 0,
        ]);

        $response = $this->actingAs($user)->put(route('habits.update', $habit), [
            'name' => 'Belajar Laravel',
        ]);

        $response->assertRedirect(route('habits.index'));
        $this->assertDatabaseHas('habits', [
            'id' => $habit->id,
            'name' => 'Belajar Laravel',
        ]);
    }

    public function test_user_cannot_update_other_users_habit()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $habit = Habit::create([
            'user_id' => $user2->id,
            'name' => 'Belajar Coding',
            'streak' => 0,
        ]);

        $response = $this->actingAs($user1)->put(route('habits.update', $habit), [
            'name' => 'Hacker Attack',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_habit()
    {
        $user = User::factory()->create();
        $habit = Habit::create([
            'user_id' => $user->id,
            'name' => 'Belajar Coding',
            'streak' => 0,
        ]);

        $response = $this->actingAs($user)->delete(route('habits.destroy', $habit));

        $response->assertRedirect(route('habits.index'));
        $this->assertDatabaseMissing('habits', [
            'id' => $habit->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_habit()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $habit = Habit::create([
            'user_id' => $user2->id,
            'name' => 'Belajar Coding',
            'streak' => 0,
        ]);

        $response = $this->actingAs($user1)->delete(route('habits.destroy', $habit));

        $response->assertStatus(403);
    }

    public function test_user_can_toggle_habit_completion()
    {
        $user = User::factory()->create(['xp' => 100, 'level' => 1]);
        $habit = Habit::create([
            'user_id' => $user->id,
            'name' => 'Belajar Coding',
            'streak' => 0,
        ]);

        // 1. Toggle Complete (Check)
        $response = $this->actingAs($user)->post(route('habits.toggle', $habit));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_completed' => true,
            'streak' => 1,
            'xp' => 120, // +20 XP
        ]);

        $this->assertDatabaseHas('habit_logs', [
            'habit_id' => $habit->id,
            'completed_date' => Carbon::today()->startOfDay()->toDateTimeString(),
        ]);

        // 2. Toggle Uncomplete (Uncheck)
        $response = $this->actingAs($user)->post(route('habits.toggle', $habit));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_completed' => false,
            'streak' => 0,
            'xp' => 100, // -20 XP
        ]);

        $this->assertDatabaseMissing('habit_logs', [
            'habit_id' => $habit->id,
            'completed_date' => Carbon::today()->startOfDay()->toDateTimeString(),
        ]);
    }
}
