<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Material;
use App\Models\FocusSession;
use App\Models\ExamPrediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Stats
        $activeTasksCount = Task::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->count();

        $focusSecondsToday = FocusSession::where('user_id', $user->id)
            ->where('completed', true)
            ->whereDate('created_at', Carbon::today())
            ->sum('duration');
        $focusMinutesToday = round($focusSecondsToday / 60);

        $materialsCount = Material::where('user_id', $user->id)->count();

        // Rata-rata kesiapan ujian (default 0% jika belum ada prediksi)
        $avgReadiness = 0;
        $predictions = ExamPrediction::where('user_id', $user->id)->get();
        if ($predictions->count() > 0) {
            $scores = [];
            foreach ($predictions as $pred) {
                if (isset($pred->hasil_prediksi['readiness_score'])) {
                    $scores[] = $pred->hasil_prediksi['readiness_score'];
                }
            }
            if (count($scores) > 0) {
                $avgReadiness = round(array_sum($scores) / count($scores));
            }
        }

        // 2. Deadline Terdekat (dalam 7 hari ke depan)
        $upcomingDeadlines = Task::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->whereNotNull('deadline')
            ->where('deadline', '>=', now())
            ->where('deadline', '<=', now()->addDays(7))
            ->orderBy('deadline', 'asc')
            ->take(5)
            ->get();

        // 3. Aktivitas Terbaru (Tasks, Materials, Focus Sessions)
        $recentActivities = collect();

        // Tasks terbaru
        Task::where('user_id', $user->id)->latest()->take(3)->get()->each(function($task) use ($recentActivities) {
            $recentActivities->push([
                'type' => 'task',
                'title' => 'Membuat tugas: ' . $task->judul,
                'time' => $task->created_at,
                'icon' => 'fa-list-check',
                'color' => 'text-primary'
            ]);
        });

        // Materi terbaru
        Material::where('user_id', $user->id)->latest()->take(3)->get()->each(function($mat) use ($recentActivities) {
            $recentActivities->push([
                'type' => 'material',
                'title' => 'Mengunggah materi: ' . $mat->judul,
                'time' => $mat->created_at,
                'icon' => 'fa-book',
                'color' => 'text-warning'
            ]);
        });

        // Sesi fokus terbaru
        FocusSession::where('user_id', $user->id)->where('completed', true)->latest()->take(3)->get()->each(function($session) use ($recentActivities) {
            $recentActivities->push([
                'type' => 'focus',
                'title' => 'Menyelesaikan fokus Pomodoro selama ' . round($session->duration / 60) . ' menit',
                'time' => $session->created_at,
                'icon' => 'fa-hourglass-half',
                'color' => 'text-success'
            ]);
        });

        // Urutkan aktivitas berdasarkan waktu terbaru
        $recentActivities = $recentActivities->sortByDesc('time')->take(5);

        // 4. Data untuk Chart.js (Produktivitas 7 hari terakhir)
        $chartLabels = [];
        $chartFocusData = [];
        $chartTasksCompleted = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->translatedFormat('l'); // Nama hari dalam bahasa Indonesia

            // Jumlah fokus dalam menit pada tanggal tersebut
            $seconds = FocusSession::where('user_id', $user->id)
                ->where('completed', true)
                ->whereDate('created_at', $date)
                ->sum('duration');
            $chartFocusData[] = round($seconds / 60);

            // Jumlah tugas yang diselesaikan pada tanggal tersebut
            $tasksDone = Task::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereDate('updated_at', $date)
                ->count();
            $chartTasksCompleted[] = $tasksDone;
        }

        return view('dashboard', compact(
            'activeTasksCount',
            'focusMinutesToday',
            'materialsCount',
            'avgReadiness',
            'upcomingDeadlines',
            'recentActivities',
            'chartLabels',
            'chartFocusData',
            'chartTasksCompleted'
        ));
    }
}
