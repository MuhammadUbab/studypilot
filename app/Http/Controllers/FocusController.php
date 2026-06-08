<?php

namespace App\Http\Controllers;

use App\Models\FocusSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FocusController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil riwayat fokus terpopuler
        $sessionsCount = FocusSession::where('user_id', $user->id)->where('completed', true)->count();
        $totalFocusSeconds = FocusSession::where('user_id', $user->id)->where('completed', true)->sum('duration');
        $totalFocusMinutes = round($totalFocusSeconds / 60);

        // Ambil daftar leaderboard regional / mock based on XP
        $leaderboard = User::orderBy('xp', 'desc')
            ->orderBy('level', 'desc')
            ->take(5)
            ->get();

        return view('focus.index', compact('sessionsCount', 'totalFocusMinutes', 'leaderboard'));
    }

    public function complete(Request $request)
    {
        $request->validate([
            'duration' => 'required|integer|min:1',
            'task_id' => 'nullable|exists:tasks,id',
        ]);

        $user = Auth::user();

        // 1. Simpan Focus Session
        $session = FocusSession::create([
            'user_id' => $user->id,
            'task_id' => $request->task_id,
            'duration' => $request->duration,
            'completed' => true,
        ]);

        // 2. Kalkulasi Streak
        // Cek apakah user sudah pernah menyelesaikan fokus hari ini (dari subuh jam 00:00)
        $alreadyFocusedToday = FocusSession::where('user_id', $user->id)
            ->where('completed', true)
            ->where('id', '!=', $session->id)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        $streakIncrement = 0;
        $newStreak = $user->streak;

        if (!$alreadyFocusedToday) {
            // Ini sesi fokus pertama hari ini, streak ditambah!
            $newStreak = $user->streak + 1;
            $streakIncrement = 1;
        }

        // 3. Tambah XP (+100 XP untuk sesi fokus lengkap)
        $xpGained = 100;
        $newXp = $user->xp + $xpGained;
        $newLevel = $user->level;
        
        $xpThreshold = $user->level * 500;
        if ($newXp >= $xpThreshold) {
            $newXp -= $xpThreshold;
            $newLevel++;
        }

        // 4. Integrasi Habit Tracker: Otomatis centang habit "Focus Session" / "Fokus" / "Pomodoro"
        $extraMessage = "";
        $habit = \App\Models\Habit::where('user_id', $user->id)
            ->where(function($query) {
                $query->where('name', 'like', '%focus%')
                      ->orWhere('name', 'like', '%pomodoro%')
                      ->orWhere('name', 'like', '%fokus%');
            })
            ->first();

        if ($habit) {
            $today = Carbon::today();
            $logExists = \App\Models\HabitLog::where('habit_id', $habit->id)
                ->whereDate('completed_date', $today)
                ->exists();

            if (!$logExists) {
                // Buat log checklist
                \App\Models\HabitLog::create([
                    'habit_id' => $habit->id,
                    'completed_date' => $today,
                ]);

                $habit->update([
                    'last_completed_at' => Carbon::now()
                ]);

                // Kalkulasi ulang streak habit
                $habitStreak = 0;
                $yesterday = Carbon::yesterday();
                $hasToday = true;
                $hasYesterday = \App\Models\HabitLog::where('habit_id', $habit->id)->whereDate('completed_date', $yesterday)->exists();
                if ($hasToday || $hasYesterday) {
                    $checkDate = $hasToday ? $today : $yesterday;
                    while (true) {
                        $exists = \App\Models\HabitLog::where('habit_id', $habit->id)->whereDate('completed_date', $checkDate)->exists();
                        if ($exists) {
                            $habitStreak++;
                            $checkDate->subDay();
                        } else {
                            break;
                        }
                    }
                }
                $habit->update(['streak' => $habitStreak]);

                // Tambah XP tambahan dari habit (+20 XP)
                $newXp += 20;
                if ($newXp >= ($newLevel * 500)) {
                    $newXp -= ($newLevel * 500);
                    $newLevel++;
                }

                $extraMessage = " Kebiasaan '" . $habit->name . "' hari ini otomatis dicentang (+20 XP)! 🎯";
            }
        }

        // Simpan perubahan ke user
        User::where('id', $user->id)->update([
            'xp' => $newXp,
            'level' => $newLevel,
            'streak' => $newStreak
        ]);

        return response()->json([
            'success' => true,
            'xp_gained' => $xpGained + ($extraMessage ? 20 : 0),
            'new_xp' => $newXp,
            'new_level' => $newLevel,
            'new_streak' => $newStreak,
            'streak_increment' => $streakIncrement,
            'message' => "Sesi fokus tersimpan! Anda mendapatkan +{$xpGained} XP!" . ($streakIncrement > 0 ? " Streak belajar Anda meningkat menjadi {$newStreak} hari! 🔥" : "") . $extraMessage
        ]);
    }
}
