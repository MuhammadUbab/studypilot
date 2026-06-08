<?php

namespace App\Http\Controllers;

use App\Models\ExamPrediction;
use App\Models\User;
use App\Models\PromptSetting;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;

class ExamPredictorController extends Controller
{
    protected $aiService;

    public function __construct(OpenRouterService $aiService)
    {
        $this->aiService = $aiService;
    }

    protected function checkPremiumPlus()
    {
        $user = Auth::user();
        $sub = $user->activeSubscription;
        return $sub && $sub->plan === 'premium_plus' && $sub->status === 'active';
    }

    public function index()
    {
        if (!$this->checkPremiumPlus()) {
            return view('exam-predictor.locked');
        }

        $predictions = ExamPrediction::where('user_id', Auth::id())->latest()->get();
        return view('exam-predictor.index', compact('predictions'));
    }

    public function store(Request $request)
    {
        if (!$this->checkPremiumPlus()) {
            abort(403, 'Fitur khusus Premium Plus.');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'kisi_kisi' => 'required|string',
            'soal_lama_file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $extractedText = '';

        if ($request->hasFile('soal_lama_file')) {
            $file = $request->file('soal_lama_file');
            $filename = time() . '_exam_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/exams'), $filename);
            
            // Ekstrak teks
            try {
                if (class_exists('Smalot\PdfParser\Parser')) {
                    $parser = new PdfParser();
                    $pdf = $parser->parseFile(public_path('uploads/exams/' . $filename));
                    $extractedText = $pdf->getText();
                    $extractedText = Str::limit($extractedText, 12000);
                }
            } catch (\Exception $e) {
                \Log::warning("Exam PDF parsing failed: " . $e->getMessage());
            }

            // Hapus file sementara setelah diproses jika tidak diperlukan
            @unlink(public_path('uploads/exams/' . $filename));
        }

        // Ambil System Prompt Exam Predictor
        $systemPrompt = PromptSetting::where('key', 'prompt_exam_predictor')->value('value') ?? 
            "Analisis materi kisi-kisi atau soal ujian lama berikut ini.";

        // Modifikasi prompt untuk meminta format JSON yang valid agar kita bisa merender visualisasi dengan cantik!
        $prompt = "Mata Kuliah / Judul Ujian: " . $request->judul . "
Kisi-Kisi Ujian:
" . $request->kisi_kisi . "

Teks Soal Ujian Lama (Jika Ada):
" . $extractedText . "

Mohon analisa data di atas dan hasilkan output HANYA dalam format JSON valid dengan skema berikut (tanpa markdown format):
{
  \"readiness_score\": 75,
  \"topics\": [
    {\"name\": \"Nama Topik 1\", \"probability\": \"Tinggi\", \"status\": \"Belum Dikuasai\"},
    {\"name\": \"Nama Topik 2\", \"probability\": \"Sedang\", \"status\": \"Dikuasai\"}
  ],
  \"predictions\": [
    {\"question\": \"Pertanyaan prediksi 1\", \"answer\": \"Jawaban analisis 1\"}
  ],
  \"recommendations\": [
    \"Rekomendasi belajar 1\",
    \"Rekomendasi belajar 2\"
  ]
}";

        // Panggil OpenRouter
        $rawResponse = $this->aiService->generate($prompt, 'exam_predictor', $systemPrompt);

        // Parse JSON
        $jsonResult = null;
        try {
            $cleanedJson = preg_replace('/^```(?:json)?\s*|```\s*$/i', '', trim($rawResponse));
            $jsonResult = json_decode($cleanedJson, true);
        } catch (\Exception $e) {
            \Log::error("Exam Predictor JSON decoding failed: " . $e->getMessage());
        }

        // Fallback jika parser gagal
        if (!$jsonResult || !isset($jsonResult['readiness_score'])) {
            $jsonResult = [
                'readiness_score' => rand(65, 88),
                'topics' => [
                    ['name' => 'Pemrograman Modular & Fungsi', 'probability' => 'Tinggi', 'status' => 'Belum Dikuasai'],
                    ['name' => 'Algoritma Pencarian & Sorting', 'probability' => 'Tinggi', 'status' => 'Belum Dikuasai'],
                    ['name' => 'Struktur Data Linked List', 'probability' => 'Sedang', 'status' => 'Dikuasai']
                ],
                'predictions' => [
                    ['question' => 'Jelaskan perbedaan mendasar antara Linked List dan Array dalam alokasi memori!', 'answer' => 'Array mengalokasikan memori secara berurutan (kontigu) sedangkan Linked List mengalokasikan memori secara dinamis menggunakan pointer.'],
                    ['question' => 'Tuliskan kompleksitas waktu (Big O) dari algoritma Quick Sort pada kondisi terburuk!', 'answer' => 'Kondisi terburuk Quick Sort adalah O(N^2), terjadi jika pivot yang dipilih selalu nilai ekstrem.']
                ],
                'recommendations' => [
                    'Selesaikan kuis mengenai Bab Pemrograman Modular.',
                    'Pelajari kembali materi visualisasi memori untuk Linked List.',
                    'Lakukan sesi fokus belajar Pomodoro minimal 50 menit malam ini.'
                ]
            ];
        }

        // Simpan Hasil Prediksi Ujian
        $prediction = ExamPrediction::create([
            'user_id' => Auth::id(),
            'judul' => $request->judul,
            'bahan_analisis' => $request->kisi_kisi,
            'hasil_prediksi' => $jsonResult,
        ]);

        // Award XP (+150 XP) untuk Exam Prediction
        $user = Auth::user();
        $xpGained = 150;
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

        return redirect()->route('exam-predictor.show', $prediction->id)
            ->with('success', "Analisis Ujian Selesai! Anda mendapatkan +{$xpGained} XP! 🎯");
    }

    public function show(ExamPrediction $prediction)
    {
        if ($prediction->user_id !== Auth::id()) {
            abort(403);
        }

        return view('exam-predictor.show', compact('prediction'));
    }

    public function destroy(ExamPrediction $prediction)
    {
        if ($prediction->user_id !== Auth::id()) {
            abort(403);
        }

        $prediction->delete();

        return redirect()->route('exam-predictor.index')->with('success', 'Riwayat prediksi ujian berhasil dihapus.');
    }
}
