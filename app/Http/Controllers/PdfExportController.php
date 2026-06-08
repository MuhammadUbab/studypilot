<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Quiz;
use App\Models\ExamPrediction;
use App\Models\Task;
use App\Models\FocusSession;
use App\Models\StudySession;
use App\Helpers\MarkdownHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfExportController extends Controller
{
    /**
     * Download PDF for Material Summary
     */
    public function exportMaterialSummary($id)
    {
        $material = Material::findOrFail($id);
        if ($material->user_id !== Auth::id()) {
            abort(403);
        }

        $user = Auth::user();
        $title = "Ringkasan Materi AI: " . $material->judul;
        $htmlContent = MarkdownHelper::toHtml($material->summary);
        $date = Carbon::now()->translatedFormat('d F Y H:i');

        $pdf = Pdf::loadView('pdf.template', compact('user', 'title', 'htmlContent', 'date'));
        return $pdf->download('Ringkasan_Materi_' . str_replace(' ', '_', $material->judul) . '.pdf');
    }

    /**
     * Download PDF for AI Study Planner (generates dynamic schedule based on active tasks)
     */
    public function exportStudyPlanner()
    {
        $user = Auth::user();
        $title = "AI Study Planner - Rencana Jadwal Belajar";
        $date = Carbon::now()->translatedFormat('d F Y H:i');

        $sessions = StudySession::where('user_id', $user->id)
            ->with('task')
            ->get()
            ->groupBy('hari');

        $htmlContent = "<h3>Rencana Jadwal Belajar Mingguan</h3>";
        $htmlContent .= "<p>Jadwal belajar ini dihasilkan secara cerdas oleh AI StudyPilot dan disesuaikan oleh pengguna berdasarkan slot waktu aktif.</p>";
        
        if ($sessions->isEmpty()) {
            $htmlContent .= "<blockquote style='border-left: 3px solid #6b7280; background: #f9fafb; padding: 10px; margin-top: 15px;'>";
            $htmlContent .= "Belum ada sesi belajar aktif terdaftar. Silakan buat rencana belajar AI di modul Study Planner terlebih dahulu.";
            $htmlContent .= "</blockquote>";
        } else {
            $htmlContent .= "<table class='data-table'>";
            $htmlContent .= "<thead><tr><th style='width: 20%;'>Hari</th><th style='width: 40%;'>Sesi Belajar</th><th style='width: 20%;'>Waktu Sesi</th><th style='width: 20%;'>Status</th></tr></thead>";
            $htmlContent .= "<tbody>";
            
            $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            foreach ($days as $day) {
                $daySessions = $sessions->get($day) ?? collect();
                $daySessions = $daySessions->sortBy('waktu_mulai');
                
                if ($daySessions->isEmpty()) {
                    continue;
                }
                
                foreach ($daySessions as $index => $session) {
                    $statusStr = $session->is_completed ? "<span style='color: #10b981; font-weight: bold;'>SELESAI</span>" : "BELUM SELESAI";
                    $linkedTask = $session->task ? "<br><small style='color: #6366f1;'>Tugas: {$session->task->judul}</small>" : "";
                    
                    $htmlContent .= "<tr>";
                    if ($index === 0) {
                        $htmlContent .= "<td rowspan='" . $daySessions->count() . "'><strong>{$day}</strong></td>";
                    }
                    $htmlContent .= "<td><strong>{$session->judul}</strong>{$linkedTask}</td>";
                    $htmlContent .= "<td>{$session->waktu_mulai} - {$session->waktu_selesai}</td>";
                    $htmlContent .= "<td>{$statusStr}</td>";
                    $htmlContent .= "</tr>";
                }
            }
            $htmlContent .= "</tbody></table>";
        }

        $pdf = Pdf::loadView('pdf.template', compact('user', 'title', 'htmlContent', 'date'));
        return $pdf->download('AI_Study_Planner_' . str_replace(' ', '_', $user->name) . '.pdf');
    }

    /**
     * Download PDF for AI Quiz Result
     */
    public function exportQuizResult($id)
    {
        $quiz = Quiz::findOrFail($id);
        if ($quiz->material->user_id !== Auth::id()) {
            abort(403);
        }

        $user = Auth::user();
        $title = "AI Quiz Result: " . $quiz->judul_quiz;
        $date = Carbon::now()->translatedFormat('d F Y H:i');

        $scoreStr = $quiz->skor !== null ? $quiz->skor . "/100" : "Belum Dikerjakan";
        
        $htmlContent = "<h3>Ringkasan Kuis</h3>";
        $htmlContent .= "<table class='data-table' style='margin-bottom: 25px;'>";
        $htmlContent .= "<tr><td style='width: 30%;'><strong>Judul Kuis:</strong></td><td>{$quiz->judul_quiz}</td></tr>";
        $htmlContent .= "<tr><td><strong>Materi Sumber:</strong></td><td>{$quiz->material->judul}</td></tr>";
        $htmlContent .= "<tr><td><strong>Total Soal:</strong></td><td>{$quiz->total_soal} Butir Soal</td></tr>";
        $htmlContent .= "<tr><td><strong>Nilai Akhir:</strong></td><td><strong style='font-size: 11.5pt; color: #6366f1;'>{$scoreStr}</strong></td></tr>";
        $htmlContent .= "</table>";

        $htmlContent .= "<h3>Lembar Tanya Jawab & Pembahasan</h3>";
        $questions = $quiz->soal_jawaban['questions'] ?? [];
        
        foreach ($questions as $index => $q) {
            $num = $index + 1;
            $htmlContent .= "<div style='margin-bottom: 15px; padding: 12px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;'>";
            $htmlContent .= "<p style='font-weight: bold; margin-bottom: 6px;'>Soal {$num}: {$q['question']}</p>";
            
            if (!empty($q['options'])) {
                $htmlContent .= "<ul style='margin-bottom: 6px;'>";
                foreach ($q['options'] as $opt) {
                    $htmlContent .= "<li>{$opt}</li>";
                }
                $htmlContent .= "</ul>";
            }
            
            $htmlContent .= "<p style='margin-bottom: 4px; color: #10b981;'><strong>Jawaban Benar:</strong> {$q['correct_answer']}</p>";
            if (isset($q['explanation'])) {
                $htmlContent .= "<p style='margin-bottom: 0; color: #555555; font-size: 9pt;'><strong>Pembahasan AI:</strong> {$q['explanation']}</p>";
            }
            $htmlContent .= "</div>";
        }

        $pdf = Pdf::loadView('pdf.template', compact('user', 'title', 'htmlContent', 'date'));
        return $pdf->download('AI_Quiz_Result_' . $quiz->id . '.pdf');
    }

    /**
     * Download PDF for AI Exam Predictor Result
     */
    public function exportExamPrediction($id)
    {
        $prediction = ExamPrediction::findOrFail($id);
        if ($prediction->user_id !== Auth::id()) {
            abort(403);
        }

        $user = Auth::user();
        $title = "AI Exam Predictor: " . $prediction->judul;
        $date = Carbon::now()->translatedFormat('d F Y H:i');

        $hasil = $prediction->hasil_prediksi;
        $readiness = $hasil['readiness_score'] ?? 0;
        
        $htmlContent = "<h3>Skor Kesiapan Ujian (Exam Readiness Score)</h3>";
        $htmlContent .= "<div style='padding: 12px 15px; background-color: #f9fafb; border-radius: 8px; border-left: 4px solid #6366f1; margin-bottom: 25px;'>";
        $htmlContent .= "<table style='width: 100%;'>";
        $htmlContent .= "<tr>";
        $htmlContent .= "<td style='width: 25%; font-size: 22pt; font-weight: bold; color: #6366f1;'>{$readiness}%</td>";
        $htmlContent .= "<td style='vertical-align: middle; font-size: 9.5pt;'>";
        $htmlContent .= "<strong>Tingkat Kesiapan Akademik</strong><br>";
        if ($readiness >= 80) {
            $htmlContent .= "Kesiapan Anda sangat baik! Lanjutkan belajar mandiri dan pelajari kisi-kisi penting di bawah ini.";
        } elseif ($readiness >= 50) {
            $htmlContent .= "Kesiapan Anda cukup. Fokuskan review pada materi kuliah yang tergolong prioritas tinggi.";
        } else {
            $htmlContent .= "Tingkat kesiapan Anda masih rendah. Tingkatkan waktu Pomodoro dan diskusikan materi dengan asisten AI.";
        }
        $htmlContent .= "</td>";
        $htmlContent .= "</tr>";
        $htmlContent .= "</table>";
        $htmlContent .= "</div>";

        $htmlContent .= "<h3>Topik Krusial yang Diprediksi Keluar</h3>";
        $htmlContent .= "<table class='data-table' style='margin-bottom: 25px;'>";
        $htmlContent .= "<thead><tr><th>Nama Topik / Bab</th><th>Tingkat Kepentingan</th></tr></thead>";
        $htmlContent .= "<tbody>";
        foreach ($hasil['topics'] ?? [] as $topic) {
            $name = $topic['name'] ?? $topic;
            $importance = $topic['importance'] ?? 'Tinggi';
            $htmlContent .= "<tr>";
            $htmlContent .= "<td><strong>{$name}</strong></td>";
            $htmlContent .= "<td><span style='color: #f59e0b; font-weight: bold;'>{$importance}</span></td>";
            $htmlContent .= "</tr>";
        }
        $htmlContent .= "</tbody></table>";

        $htmlContent .= "<h3>Prediksi Soal & Strategi Jawaban Ujian</h3>";
        foreach ($hasil['predictions'] ?? [] as $index => $pred) {
            $num = $index + 1;
            $question = $pred['question'] ?? '';
            $answer = $pred['answer_strategy'] ?? '';
            
            $htmlContent .= "<div style='margin-bottom: 15px; padding: 12px; background-color: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;'>";
            $htmlContent .= "<p style='font-weight: bold; margin-bottom: 6px;'>Soal Prediksi {$num}: {$question}</p>";
            $htmlContent .= "<p style='margin-bottom: 0; color: #555555; font-size: 9pt;'><strong>Strategi Jawaban AI:</strong> {$answer}</p>";
            $htmlContent .= "</div>";
        }

        $pdf = Pdf::loadView('pdf.template', compact('user', 'title', 'htmlContent', 'date'));
        return $pdf->download('AI_Exam_Predictor_' . $prediction->id . '.pdf');
    }

    /**
     * Download PDF for Academic Analytics
     */
    public function exportAnalytics()
    {
        $user = Auth::user();
        $title = "Laporan Analisis Akademik - StudyPilot";
        $date = Carbon::now()->translatedFormat('d F Y H:i');

        // Fetch same stats from AnalyticsController
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

        // Format stats into HTML Content
        $htmlContent = "<h3>Ringkasan Kinerja & Capaian Belajar</h3>";
        $htmlContent .= "<p>Laporan ini merangkum data historis interaksi dan capaian akademik Anda pada StudyPilot.</p>";
        
        $htmlContent .= "<table class='data-table' style='margin-bottom: 25px;'>";
        $htmlContent .= "<thead><tr><th style='width: 60%;'>Metrik Pembelajaran</th><th style='width: 40%;'>Statistik / Capaian</th></tr></thead>";
        $htmlContent .= "<tbody>";
        $htmlContent .= "<tr><td><strong>Tingkat Level Pengguna:</strong></td><td>Level {$user->level} (Total: {$user->xp} XP)</td></tr>";
        $htmlContent .= "<tr><td><strong>Total Tugas Terdaftar:</strong></td><td>{$totalTasks} Tugas ({$completedTasks} Selesai, {$activeTasks} Aktif)</td></tr>";
        $htmlContent .= "<tr><td><strong>Materi Kuliah di Knowledge Hub:</strong></td><td>{$totalMaterials} Dokumen</td></tr>";
        $htmlContent .= "<tr><td><strong>Evaluasi Kuis yang Dikerjakan:</strong></td><td>{$quizzesAttempted} Kali</td></tr>";
        $htmlContent .= "<tr><td><strong>Rata-Rata Nilai Kuis AI:</strong></td><td style='color: #6366f1; font-weight: bold;'>{$avgQuizScore} / 100</td></tr>";
        $htmlContent .= "<tr><td><strong>Total Durasi Sesi Fokus (Pomodoro):</strong></td><td>{$totalFocusHours} Jam ({$totalFocusSessions} Sesi Berhasil)</td></tr>";
        $htmlContent .= "</tbody></table>";

        // Recommendations
        $htmlContent .= "<h3>Rekomendasi Optimalisasi Akademik</h3>";
        $htmlContent .= "<blockquote style='border-left: 3px solid #6366f1; background-color: #f9fafb; padding: 10px; margin: 0;'>";
        if ($avgQuizScore < 70) {
            $htmlContent .= "• <strong>Evaluasi Pemahaman:</strong> Rata-rata nilai kuis Anda masih di bawah 70%. Sebaiknya luangkan waktu ekstra untuk meninjau AI Notes di Knowledge Hub sebelum memulai pengerjaan kuis.<br>";
        } else {
            $htmlContent .= "• <strong>Evaluasi Pemahaman:</strong> Pertahankan tingkat pemahaman materi Anda! Nilai kuis rata-rata yang tinggi menunjukkan persiapan Anda sudah matang.<br>";
        }
        if ($totalFocusHours < 5) {
            $htmlContent .= "• <strong>Sesi Pomodoro:</strong> Durasi belajar fokus Anda masih tergolong rendah. Manfaatkan widget timer Pomodoro secara konsisten agar fokus belajar tidak mudah terganggu.<br>";
        } else {
            $htmlContent .= "• <strong>Sesi Pomodoro:</strong> Kebiasaan durasi fokus Anda sudah sangat baik! Pastikan untuk menjaga pola istirahat teratur agar menghindari kejenuhan (burnout).";
        }
        $htmlContent .= "</blockquote>";

        $pdf = Pdf::loadView('pdf.template', compact('user', 'title', 'htmlContent', 'date'));
        return $pdf->download('Laporan_Akademik_' . str_replace(' ', '_', $user->name) . '.pdf');
    }
}
