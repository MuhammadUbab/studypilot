<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HabitController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $habits = Habit::where('user_id', $user->id)->with('logs')->get();

        // Hitung persentase kelengkapan hari ini
        $totalHabits = $habits->count();
        $completedToday = 0;
        foreach ($habits as $habit) {
            if ($habit->isCompletedToday()) {
                $completedToday++;
            }
        }
        $todayCompletionRate = $totalHabits > 0 ? round(($completedToday / $totalHabits) * 100) : 0;

        // Hitung streak belajar tertinggi dari semua kebiasaan
        $maxStreak = $habits->max('streak') ?? 0;

        // Siapkan data progress 7 hari terakhir (Senin-Minggu minggu ini)
        $startOfWeek = Carbon::now()->startOfWeek();
        $weeklyProgress = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            $dayName = $date->translatedFormat('D'); // e.g. Sen, Sel
            
            // Hitung berapa banyak habit yang selesai pada tanggal ini
            $doneCount = HabitLog::whereIn('habit_id', $habits->pluck('id'))
                ->whereDate('completed_date', $date)
                ->count();

            $weeklyProgress[] = [
                'date' => $dateStr,
                'day_name' => $dayName,
                'count' => $doneCount,
                'percentage' => $totalHabits > 0 ? round(($doneCount / $totalHabits) * 100) : 0
            ];
        }

        return view('habits.index', compact('habits', 'todayCompletionRate', 'maxStreak', 'weeklyProgress', 'completedToday', 'totalHabits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Habit::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'streak' => 0,
            'last_completed_at' => null,
        ]);

        return redirect()->route('habits.index')->with('success', 'Kebiasaan belajar baru berhasil ditambahkan! 🎯');
    }

    public function update(Request $request, Habit $habit)
    {
        if ($habit->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $habit->update([
            'name' => $request->name,
        ]);

        return redirect()->route('habits.index')->with('success', 'Nama kebiasaan berhasil diperbarui!');
    }

    public function destroy(Habit $habit)
    {
        if ($habit->user_id !== Auth::id()) {
            abort(403);
        }

        // Jika kebiasaan ini berkontribusi ke XP, kita biarkan saja (tidak mengurangi XP historis kecuali diinginkan)
        $habit->delete();

        return redirect()->route('habits.index')->with('success', 'Kebiasaan berhasil dihapus.');
    }

    public function toggleComplete(Habit $habit)
    {
        if ($habit->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $today = Carbon::today();
        $log = $habit->logs()->whereDate('completed_date', $today)->first();

        $user = Auth::user();
        $xpGained = 20;

        if ($log) {
            // Uncheck
            $log->delete();
            
            // Kurangi XP
            $newXp = max(0, $user->xp - $xpGained);
            $user->update(['xp' => $newXp]);
            
            $this->recalculateStreak($habit);
            $msg = "Kebiasaan ditandai belum selesai.";
            $isCompleted = false;
        } else {
            // Check
            $habit->logs()->create([
                'completed_date' => $today,
            ]);

            $habit->update([
                'last_completed_at' => Carbon::now()
            ]);

            // Tambah XP
            $newXp = $user->xp + $xpGained;
            $newLevel = $user->level;
            $xpThreshold = $user->level * 500;
            if ($newXp >= $xpThreshold) {
                $newXp -= $xpThreshold;
                $newLevel++;
            }
            $user->update([
                'xp' => $newXp,
                'level' => $newLevel
            ]);

            $this->recalculateStreak($habit);
            $msg = "Kebiasaan selesai! Anda mendapatkan +{$xpGained} XP! 🚀";
            $isCompleted = true;
        }

        // Refresh model agar memuat streak terbaru
        $habit->refresh();

        return response()->json([
            'success' => true,
            'is_completed' => $isCompleted,
            'streak' => $habit->streak,
            'xp' => $user->xp,
            'level' => $user->level,
            'message' => $msg
        ]);
    }

    protected function recalculateStreak(Habit $habit)
    {
        $streak = 0;
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $hasToday = $habit->logs()->whereDate('completed_date', $today)->exists();
        $hasYesterday = $habit->logs()->whereDate('completed_date', $yesterday)->exists();

        if ($hasToday || $hasYesterday) {
            $checkDate = $hasToday ? $today : $yesterday;
            while (true) {
                $exists = $habit->logs()->whereDate('completed_date', $checkDate)->exists();
                if ($exists) {
                    $streak++;
                    $checkDate->subDay();
                } else {
                    break;
                }
            }
        }

        $habit->update(['streak' => $streak]);
        return $streak;
    }
}
