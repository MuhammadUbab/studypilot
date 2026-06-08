<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Subscription;
use App\Models\PromptSetting;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    protected $aiService;

    public function __construct(OpenRouterService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generate(Material $material)
    {
        if ($material->user_id !== Auth::id()) {
            abort(403);
        }

        $user = Auth::user();

        // Check Subscription Limits for FREE package
        // FREE plan: max 3 quizzes per day
        $subscription = $user->activeSubscription;
        $plan = $subscription ? $subscription->plan : 'free';

        if ($plan === 'free') {
            $quizCountToday = Quiz::whereHas('material', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->whereDate('created_at', now()->today())
                ->count();

            if ($quizCountToday >= 3) {
                return back()->with('error', 'Batas harian pembuatan kuis tercapai. Hubungi kami untuk upgrade ke paket Premium Student untuk kuis tanpa batas!');
            }
        }

        // Get System Prompt for Quiz
        $systemPrompt = PromptSetting::where('key', 'prompt_quiz')->value('value') ?? 
            "Buat kuis interaktif berdasarkan materi kuliah berikut dengan format JSON yang valid.";

        $prompt = "Materi: " . $material->judul . "\n\nRingkasan Dokumen:\n" . $material->summary;

        // Call OpenRouter
        $rawResponse = $this->aiService->generate($prompt, 'quiz', $systemPrompt);

        // Parse JSON
        $quizData = null;
        try {
            // Bersihkan markdown wrappers if present (e.g. ```json ... ```)
            $cleanedJson = preg_replace('/^```(?:json)?\s*|```\s*$/i', '', trim($rawResponse));
            $quizData = json_decode($cleanedJson, true);
        } catch (\Exception $e) {
            Log::error('Quiz JSON decoding failed: ' . $e->getMessage());
        }

        // Fallback jika parser gagal
        if (!$quizData || !isset($quizData['questions']) || !is_array($quizData['questions'])) {
            // Fallback manual quiz
            $quizData = [
                'questions' => [
                    [
                        'question' => "Berdasarkan materi '" . $material->judul . "', manakah kesimpulan yang paling tepat?",
                        'type' => 'pilihan_ganda',
                        'options' => ["Konsep terstruktur adalah pilar utama", "Materi tidak relevan", "Evaluasi tidak diperlukan", "Semua salah"],
                        'correct_answer' => "Konsep terstruktur adalah pilar utama",
                        'explanation' => "Materi menekankan pentingnya efisiensi logis dan terstruktur."
                    ]
                ]
            ];
        }

        // Simpan Quiz
        $quiz = Quiz::create([
            'material_id' => $material->id,
            'judul_quiz' => 'Kuis Mandiri - ' . $material->judul,
            'total_soal' => count($quizData['questions']),
            'skor' => null,
            'soal_jawaban' => $quizData,
        ]);

        // Award XP (+50 XP) untuk generate kuis
        $xpGained = 50;
        $newXp = $user->xp + $xpGained;
        $newLevel = $user->level;
        $xpThreshold = $user->level * 500;
        if ($newXp >= $xpThreshold) {
            $newXp -= $xpThreshold;
            $newLevel++;
        }
        User::where('id', $user->id)->update([
            'xp' => $newXp,
            'level' => $newLevel
        ]);

        return redirect()->route('quizzes.show', $quiz->id)
            ->with('success', "Kuis AI berhasil dibuat! Anda mendapatkan +{$xpGained} XP! 🎉");
    }

    public function show(Quiz $quiz)
    {
        if ($quiz->material->user_id !== Auth::id()) {
            abort(403);
        }

        return view('quiz.show', compact('quiz'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        if ($quiz->material->user_id !== Auth::id()) {
            abort(403);
        }

        $answers = $request->input('answers', []);
        $questions = $quiz->soal_jawaban['questions'] ?? [];
        
        $correctCount = 0;
        $totalQuestions = count($questions);

        // Grade Quiz
        foreach ($questions as $index => $q) {
            $userAns = trim($answers[$index] ?? '');
            $correctAns = trim($q['correct_answer'] ?? '');
            
            if (strtolower($userAns) === strtolower($correctAns)) {
                $correctCount++;
            }
        }

        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;
        
        // Update Skor
        $quiz->update(['skor' => $score]);

        // Reward XP (+150 XP jika lulus > 70, +50 XP jika di bawahnya)
        $user = Auth::user();
        $xpGained = $score >= 70 ? 150 : 50;
        
        $newXp = $user->xp + $xpGained;
        $newLevel = $user->level;
        $xpThreshold = $user->level * 500;
        if ($newXp >= $xpThreshold) {
            $newXp -= $xpThreshold;
            $newLevel++;
        }
        User::where('id', $user->id)->update([
            'xp' => $newXp,
            'level' => $newLevel
        ]);

        return redirect()->route('quizzes.show', $quiz->id)
            ->with('success', "Kuis selesai! Nilai Anda: {$score}. Anda mendapatkan +{$xpGained} XP! 🎉");
    }
}
