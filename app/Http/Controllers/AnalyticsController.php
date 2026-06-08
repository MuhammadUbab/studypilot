<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\FocusSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. General Stats
        $totalTasks = Task::where('user_id', $user->id)->count();
        $completedTasks = Task::where('user_id', $user->id)->where('status', 'completed')->count();
        $activeTasks = $totalTasks - $completedTasks;

        $totalMaterials = Material::where('user_id', $user->id)->count();
        
        $quizzesAttempted = Quiz::whereHas('material', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereNotNull('skor')
            ->count();
        
        $avgQuizScore = round(Quiz::whereHas('material', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereNotNull('skor')
            ->avg('skor') ?? 0);

        $totalFocusSeconds = FocusSession::where('user_id', $user->id)->where('completed', true)->sum('duration');
        $totalFocusHours = round($totalFocusSeconds / 3600, 1);
        $totalFocusSessions = FocusSession::where('user_id', $user->id)->where('completed', true)->count();

        // 2. Productivity Trends (Last 14 Days)
        $chartLabels = [];
        $chartFocusData = [];
        $chartTasksData = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->translatedFormat('d M');

            // Focus Minutes
            $seconds = FocusSession::where('user_id', $user->id)
                ->where('completed', true)
                ->whereDate('created_at', $date)
                ->sum('duration');
            $chartFocusData[] = round($seconds / 60);

            // Tasks Completed
            $tasksCompleted = Task::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereDate('updated_at', $date)
                ->count();
            $chartTasksData[] = $tasksCompleted;
        }

        // 3. Task Status Breakdown
        $taskStatusLabels = ['Todo', 'Sedang Dikerjakan', 'Selesai'];
        $taskStatusData = [
            Task::where('user_id', $user->id)->where('status', 'todo')->count(),
            Task::where('user_id', $user->id)->where('status', 'in_progress')->count(),
            $completedTasks
        ];

        return view('analytics.index', compact(
            'totalTasks',
            'completedTasks',
            'activeTasks',
            'totalMaterials',
            'quizzesAttempted',
            'avgQuizScore',
            'totalFocusHours',
            'totalFocusSessions',
            'chartLabels',
            'chartFocusData',
            'chartTasksData',
            'taskStatusLabels',
            'taskStatusData'
        ));
    }
}
