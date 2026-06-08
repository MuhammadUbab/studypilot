<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Task::where('user_id', $user->id);

        // Filter Status
        if ($request->has('status') && in_array($request->status, ['todo', 'in_progress', 'completed'])) {
            $query->where('status', $request->status);
        }

        // Filter Prioritas
        if ($request->has('prioritas') && in_array($request->prioritas, ['low', 'medium', 'high'])) {
            $query->where('prioritas', $request->prioritas);
        }

        // Urutkan berdasarkan deadline terdekat, lalu prioritas
        $tasks = $query->orderByRaw("CASE WHEN deadline IS NULL THEN 1 ELSE 0 END")
            ->orderBy('deadline', 'asc')
            ->orderByRaw("CASE prioritas WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END")
            ->get();

        return view('tasks.index', compact('tasks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'deadline' => 'nullable|date',
            'prioritas' => 'required|string|in:low,medium,high',
        ]);

        Task::create([
            'user_id' => Auth::id(),
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'deadline' => $request->deadline,
            'prioritas' => $request->prioritas,
            'status' => 'todo',
        ]);

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil ditambahkan!');
    }

    public function update(Request $request, Task $task)
    {
        // Validasi kepemilikan tugas
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'deadline' => 'nullable|date',
            'prioritas' => 'required|string|in:low,medium,high',
            'status' => 'required|string|in:todo,in_progress,completed',
        ]);

        $oldStatus = $task->status;

        $task->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'deadline' => $request->deadline,
            'prioritas' => $request->prioritas,
            'status' => $request->status,
        ]);

        // Beri XP jika baru menyelesaikan tugas
        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            $user = Auth::user();
            $xpGained = 50; // Menyelesaikan tugas memberikan +50 XP
            $newXp = $user->xp + $xpGained;
            $newLevel = $user->level;

            // Naik level setiap kelipatan level * 500 XP
            $xpThreshold = $user->level * 500;
            if ($newXp >= $xpThreshold) {
                $newXp -= $xpThreshold;
                $newLevel++;
            }

            User::where('id', $user->id)->update([
                'xp' => $newXp,
                'level' => $newLevel
            ]);

            return redirect()->route('tasks.index')->with('success', "Tugas diselesaikan! Anda mendapatkan +{$xpGained} XP! 🎉");
        }

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil diperbarui!');
    }

    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dihapus.');
    }

    public function toggleStatus(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $newStatus = $task->status === 'completed' ? 'todo' : 'completed';
        $task->update(['status' => $newStatus]);

        $xpGained = 0;
        if ($newStatus === 'completed') {
            $user = Auth::user();
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
        }

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'xp_gained' => $xpGained,
            'message' => $newStatus === 'completed' ? "Tugas selesai! +{$xpGained} XP" : "Tugas dikembalikan ke antrean."
        ]);
    }

    // AI Prioritize Heuristic Helper (Tahap 2)
    public function aiPrioritize(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Analisis konten secara cerdas
        $judul = strtolower($task->judul);
        $deskripsi = strtolower($task->deskripsi ?? '');
        $deadline = $task->deadline;

        $score = 0;

        // Faktor Kata Kunci
        if (str_contains($judul, 'ujian') || str_contains($judul, 'uts') || str_contains($judul, 'uas') || str_contains($judul, 'exam') || str_contains($judul, 'kristis')) {
            $score += 5;
        }
        if (str_contains($judul, 'tugas besar') || str_contains($judul, 'tubes') || str_contains($judul, 'proyek') || str_contains($judul, 'project')) {
            $score += 3;
        }
        if (str_contains($judul, 'kuis') || str_contains($judul, 'quiz') || str_contains($judul, 'pr') || str_contains($judul, 'homework')) {
            $score += 2;
        }

        // Faktor Deadline
        if ($deadline) {
            $daysLeft = now()->diffInDays($deadline, false);
            if ($daysLeft < 0) {
                // Lewat deadline
                $score += 0;
            } elseif ($daysLeft <= 1) {
                $score += 8; // Besok atau hari ini
            } elseif ($daysLeft <= 3) {
                $score += 5;
            } elseif ($daysLeft <= 7) {
                $score += 2;
            }
        }

        // Tentukan prioritas
        $priority = 'medium';
        $explanation = 'AI menganalisis bahwa tugas ini memiliki kompleksitas sedang dan tenggat waktu yang cukup wajar.';

        if ($score >= 8) {
            $priority = 'high';
            $explanation = 'AI mendeteksi tingkat kepentingan yang sangat tinggi! Tugas ini terkait dengan ujian penting atau memiliki tenggat waktu yang sangat mendesak (< 3 hari). Disarankan untuk dikerjakan segera.';
        } elseif ($score <= 2) {
            $priority = 'low';
            $explanation = 'AI mengklasifikasikan tugas ini berprioritas rendah karena tenggat waktu masih cukup lama (> 7 hari) dan tingkat kesulitan relatif ringan.';
        } else {
            $priority = 'medium';
            $explanation = 'AI menganalisis bahwa tugas ini memiliki urgensi tingkat menengah. Tenggat waktu berada di kisaran 3-7 hari.';
        }

        // Simpan prioritas baru
        $task->update(['prioritas' => $priority]);

        return response()->json([
            'success' => true,
            'prioritas' => $priority,
            'explanation' => $explanation
        ]);
    }
}
