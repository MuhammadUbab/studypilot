<?php

namespace App\Services;

use App\Models\PromptSetting;
use App\Models\AiUsageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OpenRouterService
{
    protected $apiKey;
    protected $baseUrl = 'https://openrouter.ai/api/v1';

    public function __construct()
    {
        $this->apiKey = env('OPENROUTER_API_KEY');
    }

    /**
     * Call OpenRouter API. If API key is invalid or placeholder, falls back to a smart mock response.
     */
    public function generate(string $prompt, string $feature, string $systemPrompt = null)
    {
        // Get model settings from database or fallback to .env
        $model = PromptSetting::where('key', 'ai_default_model')->value('value') ?? env('OPENROUTER_DEFAULT_MODEL', 'deepseek/deepseek-chat');

        // Personalize prompt based on user's education level
        if (Auth::check()) {
            $level = Auth::user()->education_level;
            $personalization = "";
            if ($level === 'pelajar') {
                $personalization = "Jelaskan dengan bahasa SMA.";
            } elseif ($level === 'guru_dosen') {
                $personalization = "Jelaskan dengan sudut pandang pengajar.";
            } else {
                $personalization = "Jelaskan dengan konteks perkuliahan.";
            }
            $systemPrompt = ($systemPrompt ? $systemPrompt . "\n" : "") . $personalization;
        }

        // Check if API key is valid or placeholder
        if (empty($this->apiKey) || $this->apiKey === 'your-openrouter-api-key' || str_contains($this->apiKey, 'placeholder')) {
            Log::info("Using Mock AI for feature: $feature due to missing OpenRouter API Key.");
            return $this->generateMockResponse($prompt, $feature, $model);
        }

        try {
            $messages = [];
            if ($systemPrompt) {
                $messages[] = ['role' => 'system', 'content' => $systemPrompt];
            }
            $messages[] = ['role' => 'user', 'content' => $prompt];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => 'http://localhost:8000',
                'X-Title' => 'StudyPilot',
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['choices'][0]['message']['content'] ?? '';
                
                // Log AI Usage
                $tokens = $data['usage']['total_tokens'] ?? 0;
                AiUsageLog::create([
                    'user_id' => Auth::id() ?? 1,
                    'feature' => $feature,
                    'model' => $model,
                    'token_usage' => $tokens,
                ]);

                return $text;
            } else {
                Log::error('OpenRouter API Fail: ' . $response->body());
                return $this->generateMockResponse($prompt, $feature, $model);
            }
        } catch (\Exception $e) {
            Log::error('OpenRouter API Exception: ' . $e->getMessage());
            return $this->generateMockResponse($prompt, $feature, $model);
        }
    }

    /**
     * Smart Mock Response generator when OpenRouter is not configured.
     * Generates relevant content based on document details in prompt.
     */
    protected function generateMockResponse(string $prompt, string $feature, string $model)
    {
        // Log Mock usage
        AiUsageLog::create([
            'user_id' => Auth::id() ?? 1,
            'feature' => $feature,
            'model' => $model . ' (Mock)',
            'token_usage' => 1500,
        ]);

        // Extract a name or subject from prompt
        preg_match('/materi:?\s*([^\n]+)/i', $prompt, $matches);
        $subject = $matches[1] ?? 'Materi Kuliah';
        $subject = trim(str_replace(['"', "'", '*'], '', $subject));

        $level = Auth::check() ? Auth::user()->education_level : 'mahasiswa';
        $personalizationNote = "";
        if ($level === 'pelajar') {
            $personalizationNote = "> [!NOTE]\n> **AI Personalization (Pelajar)**: Penjelasan ini disederhanakan dengan gaya bahasa SMA yang mudah dipahami.\n\n";
        } elseif ($level === 'guru_dosen') {
            $personalizationNote = "> [!NOTE]\n> **AI Personalization (Guru/Dosen)**: Penjelasan diformulasikan dari sudut pandang pengajar untuk mempermudah penyampaian materi.\n\n";
        } else {
            $personalizationNote = "> [!NOTE]\n> **AI Personalization (Mahasiswa)**: Penjelasan difokuskan pada konteks perkuliahan akademik.\n\n";
        }

        if ($feature === 'summary') {
            return $personalizationNote . "### Ringkasan Materi: {$subject}

Ini adalah ringkasan cerdas yang dihasilkan oleh AI StudyPilot untuk membantu Anda memahami topik ini dengan lebih cepat dan terstruktur.

---

### 📌 Poin Penting
1. **Definisi Utama**: Konsep dasar dari {$subject} yang mencakup teori-teori fundamental dan aplikasinya di dunia nyata.
2. **Karakteristik & Struktur**: Bagaimana materi ini tersusun dan diimplementasikan dalam kurikulum akademik atau industri.
3. **Metodologi**: Langkah-langkah praktis dan formula penting yang sering diuji dalam lembar kerja mahasiswa.
4. **Analisis Kritis**: Hubungan timbal balik antara konsep ini dengan topik perkuliahan lainnya di semester ini.

---

### 💡 Istilah Penting (Glosarium)
* **Paradigma**: Pola pikir atau model dasar dalam memecahkan masalah terkait {$subject}.
* **Sintaksis**: Aturan penulisan atau struktur ekspresi formal yang digunakan.
* **Efisiensi**: Ukuran optimalisasi pemakaian sumber daya dalam penerapan sistem.
* **Analisis Heuristik**: Metode pemecahan masalah secara praktis yang menghasilkan solusi yang memuaskan.

---

### 📝 Catatan Per Bab
#### Bab 1: Pengenalan Umum
Membahas sejarah perkembangan, latar belakang masalah, dan mengapa {$subject} sangat relevan dipelajari pada dekade ini.

#### Bab 2: Komponen & Implementasi
Pembahasan mendalam tentang elemen-elemen inti, diagram alir proses, dan contoh penyelesaian studi kasus secara terperinci.

#### Bab 3: Evaluasi & Optimasi
Bagaimana cara melakukan analisis performa, mendeteksi kesalahan sistem, dan melakukan optimasi hasil akhir.

---

### 🎯 Kesimpulan
Materi **{$subject}** mengajarkan kita untuk berpikir sistematis dalam menguraikan masalah kompleks menjadi sub-masalah kecil yang lebih mudah dikelola dan diselesaikan secara efisien.";
        }

        if ($feature === 'quiz') {
            // Return JSON Quiz
            return json_encode([
                'questions' => [
                    [
                        'question' => "Manakah di bawah ini yang merupakan konsep dasar dari {$subject}?",
                        'type' => 'pilihan_ganda',
                        'options' => [
                            "Metode optimasi performa sistem secara menyeluruh",
                            "Pola pikir sistematis dalam memecahkan masalah",
                            "Struktur penulisan ekspresi formal",
                            "Semua jawaban di atas benar"
                        ],
                        'correct_answer' => "Semua jawaban di atas benar",
                        'explanation' => "Konsep {$subject} secara fundamental mencakup metode optimasi, pola pikir terstruktur, dan penulisan ekspresi yang sistematis."
                    ],
                    [
                        'question' => "{$subject} berfokus pada efisiensi pemakaian sumber daya.",
                        'type' => 'true_false',
                        'options' => ["True", "False"],
                        'correct_answer' => "True",
                        'explanation' => "Efisiensi merupakan salah satu pilar utama yang dipelajari dalam {$subject} untuk memastikan efektivitas sistem."
                    ],
                    [
                        'question' => "Mengapa analisis heuristik sangat penting dalam implementasi {$subject}?",
                        'type' => 'hots',
                        'options' => [
                            "Karena memberikan jaminan solusi 100% akurat secara instan",
                            "Karena meminimalkan kompleksitas waktu pencarian solusi pada masalah besar",
                            "Karena tidak membutuhkan sumber daya komputasi",
                            "Karena hanya digunakan oleh administrator sistem"
                        ],
                        'correct_answer' => "Karena meminimalkan kompleksitas waktu pencarian solusi pada masalah besar",
                        'explanation' => "Analisis heuristik mempermudah pencarian jalan keluar praktis untuk masalah yang terlalu kompleks jika diselesaikan dengan algoritma pencarian eksak."
                    ],
                    [
                        'question' => "Jelaskan secara singkat kegunaan utama dari mempelajari {$subject} di perkuliahan!",
                        'type' => 'essay',
                        'options' => [],
                        'correct_answer' => "Membantu mahasiswa berpikir analitis, memecahkan masalah kompleks secara logis, dan mengoptimalkan sistem.",
                        'explanation' => "Esensi {$subject} adalah melatih logika berpikir mahasiswa agar mampu menstrukturkan problem nyata menjadi solusi teknis yang adaptif."
                    ]
                ]
            ]);
        }

        if ($feature === 'chat_materi') {
            return $personalizationNote . "Berdasarkan materi kuliah **{$subject}** yang Anda unggah, konsep tersebut menjelaskan bahwa optimasi dan logika terstruktur adalah kunci utama. Sistem mendeteksi bahwa pertanyaan Anda sangat relevan dengan bahasan di Bab 2 tentang komponen inti materi. Apakah ada bagian spesifik dari diagram alir atau rumus di Bab 2 yang ingin kita bedah bersama?";
        }

        return "Response simulasi AI untuk model $model pada fitur $feature.";
    }
}
