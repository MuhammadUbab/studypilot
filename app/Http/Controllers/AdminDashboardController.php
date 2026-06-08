<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\Material;
use App\Models\Quiz;
use App\Models\Subscription;
use App\Models\AiUsageLog;
use App\Models\FocusSession;
use App\Models\PromptSetting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $premiumUsers = Subscription::where('plan', '!=', 'free')
            ->where('status', 'active')
            ->count();
        $totalMaterials = Material::count();
        $totalQuizzes = Quiz::count();
        
        $totalAiRequests = AiUsageLog::count();
        
        $totalFocusSeconds = FocusSession::where('completed', true)->sum('duration');
        $totalFocusHours = round($totalFocusSeconds / 3600, 1);

        // API settings
        $defaultModel = PromptSetting::where('key', 'ai_default_model')->value('value') ?? 'deepseek/deepseek-chat';

        // Chart Data: AI Usage breakdown
        $features = ['summary', 'quiz', 'planner', 'exam_predictor', 'chat_materi'];
        $aiUsageLabels = ['Ringkasan', 'Kuis', 'Planner', 'Predictor', 'Chat'];
        $aiUsageData = [];
        
        foreach ($features as $feat) {
            $aiUsageData[] = AiUsageLog::where('feature', $feat)->count();
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'premiumUsers',
            'totalMaterials',
            'totalQuizzes',
            'totalAiRequests',
            'totalFocusHours',
            'defaultModel',
            'aiUsageLabels',
            'aiUsageData'
        ));
    }

    public function users(Request $request)
    {
        $search = $request->input('search');
        $query = User::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        $users = $query->latest()->get();
        return view('admin.users', compact('users', 'search'));
    }

    public function toggleUserRole(User $user)
    {
        // Cegah mengubah role diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mengubah hak akses akun Anda sendiri.');
        }

        $newRole = $user->role === 'admin' ? 'user' : 'admin';
        $user->update(['role' => $newRole]);

        return back()->with('success', "Role pengguna {$user->name} berhasil diubah menjadi {$newRole}.");
    }

    public function suspendUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mensuspensi akun Anda sendiri.');
        }

        $newSuspended = !$user->is_suspended;
        $user->update(['is_suspended' => $newSuspended]);

        $statusText = $newSuspended ? 'ditangguhkan' : 'diaktifkan kembali';
        return back()->with('success', "Akun pengguna {$user->name} berhasil {$statusText}.");
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return back()->with('success', "Akun pengguna {$user->name} berhasil dihapus permanen.");
    }

    public function subscriptions()
    {
        $premiumUsersList = Subscription::where('plan', '!=', 'free')
            ->where('status', 'active')
            ->with('user')
            ->latest()
            ->get();
            
        return view('admin.subscriptions', compact('premiumUsersList'));
    }

    public function aiUsage()
    {
        $totalRequests = AiUsageLog::count();
        $totalTokens = AiUsageLog::sum('token_usage');
        
        // Simulasikan estimasi biaya: $0.0002 per 1K token
        $estimatedCost = round(($totalTokens / 1000) * 0.0002, 4);

        $modelStats = AiUsageLog::selectRaw('model, count(*) as count')
            ->groupBy('model')
            ->orderBy('count', 'desc')
            ->get();

        $recentLogs = AiUsageLog::with('user')
            ->latest()
            ->take(15)
            ->get();

        return view('admin.ai-usage', compact(
            'totalRequests',
            'totalTokens',
            'estimatedCost',
            'modelStats',
            'recentLogs'
        ));
    }

    public function prompts()
    {
        $promptSummary = PromptSetting::where('key', 'prompt_summary')->first();
        $promptQuiz = PromptSetting::where('key', 'prompt_quiz')->first();
        $promptStudyPlanner = PromptSetting::where('key', 'prompt_study_planner')->first();
        $promptExamPredictor = PromptSetting::where('key', 'prompt_exam_predictor')->first();
        $promptChatMateri = PromptSetting::where('key', 'prompt_chat_materi')->first();

        return view('admin.prompts', compact(
            'promptSummary',
            'promptQuiz',
            'promptStudyPlanner',
            'promptExamPredictor',
            'promptChatMateri'
        ));
    }

    public function updatePrompts(Request $request)
    {
        $request->validate([
            'prompt_summary' => 'required|string',
            'prompt_quiz' => 'required|string',
            'prompt_study_planner' => 'required|string',
            'prompt_exam_predictor' => 'required|string',
            'prompt_chat_materi' => 'required|string',
        ]);

        PromptSetting::updateOrCreate(['key' => 'prompt_summary'], ['value' => $request->prompt_summary]);
        PromptSetting::updateOrCreate(['key' => 'prompt_quiz'], ['value' => $request->prompt_quiz]);
        PromptSetting::updateOrCreate(['key' => 'prompt_study_planner'], ['value' => $request->prompt_study_planner]);
        PromptSetting::updateOrCreate(['key' => 'prompt_exam_predictor'], ['value' => $request->prompt_exam_predictor]);
        PromptSetting::updateOrCreate(['key' => 'prompt_chat_materi'], ['value' => $request->prompt_chat_materi]);

        return back()->with('success', 'Semua System Prompt AI berhasil diperbarui!');
    }

    public function aiSettings()
    {
        $defaultModel = PromptSetting::where('key', 'ai_default_model')->value('value') ?? 'deepseek/deepseek-chat';
        return view('admin.ai-settings', compact('defaultModel'));
    }

    public function updateAiSettings(Request $request)
    {
        $request->validate([
            'default_model' => 'required|string',
        ]);

        PromptSetting::updateOrCreate(
            ['key' => 'ai_default_model'],
            ['value' => $request->default_model, 'description' => 'Model AI default yang digunakan di platform StudyPilot.']
        );

        return back()->with('success', 'Model AI default berhasil diperbarui!');
    }
}
