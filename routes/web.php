<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\KnowledgeHubController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\FocusController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ExamPredictorController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\StudyPlannerController;
use App\Http\Controllers\HabitController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Guest / Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    
    Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Authenticated Routes (User & Admin)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // User Profile
    Route::get('/profile', [AuthController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/theme', [AuthController::class, 'updateTheme'])->name('profile.theme');

    // User Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Smart Task Management
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/{task}/toggle', [TaskController::class, 'toggleStatus'])->name('tasks.toggle');
    Route::post('/tasks/{task}/ai-prioritize', [TaskController::class, 'aiPrioritize'])->name('tasks.ai-prioritize');

    // AI Study Planner
    Route::get('/study-planner', [StudyPlannerController::class, 'index'])->name('study-planner.index');
    Route::post('/study-planner/generate', [StudyPlannerController::class, 'generate'])->name('study-planner.generate');
    Route::post('/study-planner', [StudyPlannerController::class, 'store'])->name('study-planner.store');
    Route::delete('/study-planner/clear', [StudyPlannerController::class, 'clear'])->name('study-planner.clear');
    Route::put('/study-planner/{session}', [StudyPlannerController::class, 'update'])->name('study-planner.update');
    Route::delete('/study-planner/{session}', [StudyPlannerController::class, 'destroy'])->name('study-planner.destroy');
    Route::post('/study-planner/{session}/toggle', [StudyPlannerController::class, 'toggleComplete'])->name('study-planner.toggle');

    // Habit Tracker
    Route::get('/habits', [HabitController::class, 'index'])->name('habits.index');
    Route::post('/habits', [HabitController::class, 'store'])->name('habits.store');
    Route::put('/habits/{habit}', [HabitController::class, 'update'])->name('habits.update');
    Route::delete('/habits/{habit}', [HabitController::class, 'destroy'])->name('habits.destroy');
    Route::post('/habits/{habit}/toggle', [HabitController::class, 'toggleComplete'])->name('habits.toggle');

    // Knowledge Hub
    Route::get('/knowledge-hub', [KnowledgeHubController::class, 'index'])->name('knowledge-hub.index');
    Route::post('/knowledge-hub', [KnowledgeHubController::class, 'store'])->name('knowledge-hub.store');
    Route::get('/knowledge-hub/{material}', [KnowledgeHubController::class, 'show'])->name('knowledge-hub.show');
    Route::delete('/knowledge-hub/{material}', [KnowledgeHubController::class, 'destroy'])->name('knowledge-hub.destroy');
    Route::post('/knowledge-hub/{material}/chat', [KnowledgeHubController::class, 'chat'])->name('knowledge-hub.chat');

    // AI Quiz Generator
    Route::post('/quizzes/generate/{material}', [QuizController::class, 'generate'])->name('quizzes.generate');
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit'])->name('quizzes.submit');

    // Focus Mode (Pomodoro)
    Route::get('/focus', [FocusController::class, 'index'])->name('focus.index');
    Route::post('/focus/complete', [FocusController::class, 'complete'])->name('focus.complete');

    // Academic Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Subscriptions Pricing & Payment
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');

    // AI Exam Predictor
    Route::get('/exam-predictor', [ExamPredictorController::class, 'index'])->name('exam-predictor.index');
    Route::post('/exam-predictor', [ExamPredictorController::class, 'store'])->name('exam-predictor.store');
    Route::get('/exam-predictor/{prediction}', [ExamPredictorController::class, 'show'])->name('exam-predictor.show');
    Route::delete('/exam-predictor/{prediction}', [ExamPredictorController::class, 'destroy'])->name('exam-predictor.destroy');

    // Export PDF Routes
    Route::get('/knowledge-hub/{material}/pdf', [PdfExportController::class, 'exportMaterialSummary'])->name('materials.pdf');
    Route::get('/study-planner/pdf', [PdfExportController::class, 'exportStudyPlanner'])->name('study-planner.pdf');
    Route::get('/quizzes/{quiz}/pdf', [PdfExportController::class, 'exportQuizResult'])->name('quizzes.pdf');
    Route::get('/exam-predictor/{prediction}/pdf', [PdfExportController::class, 'exportExamPrediction'])->name('exam-predictor.pdf');
    Route::get('/analytics/pdf', [PdfExportController::class, 'exportAnalytics'])->name('analytics.pdf');
});

// Admin Only Routes (Protected by auth and admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    Route::post('/users/{user}/toggle-role', [AdminDashboardController::class, 'toggleUserRole'])->name('users.toggle-role');
    Route::post('/users/{user}/suspend', [AdminDashboardController::class, 'suspendUser'])->name('users.suspend');
    Route::delete('/users/{user}', [AdminDashboardController::class, 'destroyUser'])->name('users.destroy');

    // Subscription Management
    Route::get('/subscriptions', [AdminDashboardController::class, 'subscriptions'])->name('subscriptions');

    // AI Usage Monitoring
    Route::get('/ai-usage', [AdminDashboardController::class, 'aiUsage'])->name('ai-usage');

    // Prompt Management
    Route::get('/prompts', [AdminDashboardController::class, 'prompts'])->name('prompts');
    Route::post('/prompts', [AdminDashboardController::class, 'updatePrompts'])->name('prompts.update');

    // AI Settings
    Route::get('/ai-settings', [AdminDashboardController::class, 'aiSettings'])->name('ai-settings');
    Route::post('/ai-settings', [AdminDashboardController::class, 'updateAiSettings'])->name('ai-settings.update');
});
