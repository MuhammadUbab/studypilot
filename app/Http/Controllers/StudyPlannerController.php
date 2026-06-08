<?php

namespace App\Http\Controllers;

use App\Models\StudySession;
use App\Models\Task;
use App\Models\User;
use App\Models\PromptSetting;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudyPlannerController extends Controller
{
    protected $aiService;

    public function __construct(OpenRouterService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $user = Auth::user();
        $tasks = Task::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->orderBy('deadline', 'asc')
            ->get();

        // Ambil semua sesi belajar dan kelompokkan berdasarkan Hari
        $sessions = StudySession::where('user_id', $user->id)
            ->with('task')
            ->get()
            ->groupBy('hari');

        return view('study-planner.index', compact('tasks', 'sessions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'hari' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu_mulai' => 'required|string',
            'waktu_selesai' => 'required|string',
            'task_id' => 'nullable|exists:tasks,id',
        ]);

        StudySession::create([
            'user_id' => Auth::id(),
            'task_id' => $request->task_id,
            'judul' => $request->judul,
            'hari' => $request->hari,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'is_completed' => false,
        ]);

        return redirect()->route('study-planner.index')->with('success', 'Sesi belajar baru berhasil ditambahkan! 📅');
    }

    public function update(Request $request, StudySession $session)
    {
        if ($session->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'hari' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu_mulai' => 'required|string',
            'waktu_selesai' => 'required|string',
            'task_id' => 'nullable|exists:tasks,id',
        ]);

        $session->update([
            'task_id' => $request->task_id,
            'judul' => $request->judul,
            'hari' => $request->hari,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
        ]);

        return redirect()->route('study-planner.index')->with('success', 'Sesi belajar berhasil diperbarui!');
    }

    public function destroy(StudySession $session)
    {
        if ($session->user_id !== Auth::id()) {
            abort(403);
        }

        // Jika selesai, kurangi XP sebelum dihapus
        if ($session->is_completed) {
            $user = Auth::user();
            $newXp = max(0, $user->xp - 30);
            $user->update(['xp' => $newXp]);
        }

        $session->delete();

        return redirect()->route('study-planner.index')->with('success', 'Sesi belajar berhasil dihapus.');
    }

    public function toggleComplete(StudySession $session)
    {
        if ($session->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $session->is_completed = !$session->is_completed;
        $session->save();

        // XP Reward (+30 XP)
        $user = Auth::user();
        $xpGained = 30;
        
        if ($session->is_completed) {
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
            $msg = "Sesi belajar selesai! Anda mendapatkan +{$xpGained} XP! 🎯";
        } else {
            $newXp = max(0, $user->xp - $xpGained);
            $user->update(['xp' => $newXp]);
            $msg = "Sesi belajar ditandai belum selesai.";
        }

        return response()->json([
            'success' => true,
            'is_completed' => $session->is_completed,
            'xp' => $user->xp,
            'level' => $user->level,
            'message' => $msg
        ]);
    }

    public function generate()
    {
        $user = Auth::user();
        
        // Hapus rencana belajar lama agar bersih
        StudySession::where('user_id', $user->id)->delete();

        // Dapatkan tugas aktif
        $tasks = Task::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->orderBy('deadline', 'asc')
            ->get();

        // System Prompt untuk AI Planner
        $systemPrompt = PromptSetting::where('key', 'prompt_study_planner')->value('value') ?? 
            "Anda adalah AI Study Planner. Jadwalkan sesi belajar mingguan berdasarkan tugas aktif mahasiswa.";

        $prompt = "Mahasiswa: " . $user->name . " (Level pendidikan: " . ($user->education_level ?? 'mahasiswa') . ")
Daftar Tugas Aktif:
";
        foreach ($tasks as $task) {
            $prompt .= "- ID: {$task->id}, Judul: {$task->judul}, Prioritas: {$task->prioritas}, Tenggat: " . ($task->deadline ? $task->deadline->format('Y-m-d H:i') : '-') . "\n";
        }

        $prompt .= "
Mohon rancang jadwal belajar mingguan terstruktur (Senin sampai Jumat, 3 sesi per hari: Pagi 09:00 - 11:30, Siang 13:30 - 15:30, Malam 19:00 - 21:00).
Kembalikan HANYA dalam format JSON valid dengan skema berikut:
{
  \"sessions\": [
    {\"judul\": \"Review Materi X\", \"hari\": \"Senin\", \"waktu_mulai\": \"09:00\", \"waktu_selesai\": \"11:30\", \"task_id\": 1},
    {\"judul\": \"Fokus Mandiri Pomodoro\", \"hari\": \"Senin\", \"waktu_mulai\": \"13:30\", \"waktu_selesai\": \"15:30\", \"task_id\": null}
  ]
}";

        // Panggil OpenRouter
        $rawResponse = $this->aiService->generate($prompt, 'planner', $systemPrompt);

        // Parse JSON
        $jsonData = null;
        try {
            $cleanedJson = preg_replace('/^```(?:json)?\s*|```\s*$/i', '', trim($rawResponse));
            $jsonData = json_decode($cleanedJson, true);
        } catch (\Exception $e) {
            Log::error('AI Study Planner JSON decoding failed: ' . $e->getMessage());
        }

        // Fallback jika parser gagal atau menggunakan mock
        if (!$jsonData || !isset($jsonData['sessions']) || !is_array($jsonData['sessions'])) {
            $jsonData = $this->generateMockPlannerData($tasks);
        }

        // Dapatkan semua ID tugas milik user untuk divalidasi secara in-memory (menghindari N+1 query ke Supabase)
        $userTaskIds = Task::where('user_id', $user->id)->pluck('id')->toArray();

        // Simpan sesi belajar ke database
        foreach ($jsonData['sessions'] as $sessionData) {
            // Pastikan task_id yang dirujuk valid milik user
            $taskId = $sessionData['task_id'] ?? null;
            if ($taskId && !in_array($taskId, $userTaskIds)) {
                $taskId = null;
            }

            StudySession::create([
                'user_id' => $user->id,
                'task_id' => $taskId,
                'judul' => $sessionData['judul'] ?? 'Sesi Belajar Mandiri',
                'hari' => $sessionData['hari'] ?? 'Senin',
                'waktu_mulai' => $sessionData['waktu_mulai'] ?? '09:00',
                'waktu_selesai' => $sessionData['waktu_selesai'] ?? '11:00',
                'is_completed' => false,
            ]);
        }

        return redirect()->route('study-planner.index')->with('success', 'Rencana belajar AI mingguan berhasil dibuat dan disimpan! 🚀');
    }

    protected function generateMockPlannerData($tasks)
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $slots = [
            ['start' => '09:00', 'end' => '11:30', 'label' => 'Sesi Pagi'],
            ['start' => '13:30', 'end' => '15:30', 'label' => 'Sesi Siang'],
            ['start' => '19:00', 'end' => '21:00', 'label' => 'Sesi Malam']
        ];

        $sessions = [];
        $taskIndex = 0;
        $totalTasks = count($tasks);

        foreach ($days as $day) {
            foreach ($slots as $slot) {
                if ($taskIndex < $totalTasks) {
                    $task = $tasks[$taskIndex];
                    $sessions[] = [
                        'judul' => 'Smart Task Review: ' . $task->judul,
                        'hari' => $day,
                        'waktu_mulai' => $slot['start'],
                        'waktu_selesai' => $slot['end'],
                        'task_id' => $task->id
                    ];
                    $taskIndex++;
                } else {
                    // Sesi Generic
                    $genericTitles = [
                        'Review Rangkuman Materi AI Notes',
                        'Fokus Mandiri Pomodoro 25 Menit',
                        'Tanya Jawab AI Chat Materi',
                        'Simulasi Evaluasi Kuis AI',
                        'Review Kisi-kisi Prediksi Ujian'
                    ];
                    $sessions[] = [
                        'judul' => $genericTitles[array_rand($genericTitles)],
                        'hari' => $day,
                        'waktu_mulai' => $slot['start'],
                        'waktu_selesai' => $slot['end'],
                        'task_id' => null
                    ];
                }
            }
        }

        return ['sessions' => $sessions];
    }
}
