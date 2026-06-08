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
        $this->apiKey = config('services.openrouter.api_key');
    }

    /**
     * Call OpenRouter API. If API key is invalid or placeholder, falls back to a smart mock response.
     */
    public function generate(string $prompt, string $feature, string $systemPrompt = null)
    {
        // Get model settings from database or fallback to config
        $model = PromptSetting::where('key', 'ai_default_model')->value('value') ?? config('services.openrouter.default_model');

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
                'max_tokens' => 2000,
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
            $summaryText = "";
            if ($systemPrompt && str_contains($systemPrompt, "RINGKASAN DOKUMEN MATERI:")) {
                $parts = explode("RINGKASAN DOKUMEN MATERI:", $systemPrompt);
                $summaryText = trim($parts[1] ?? "");
            }

            if (!empty($summaryText)) {
                // Bersihkan query user
                $query = strtolower($prompt);
                // Tokenisasi query ke kata-kata penting
                $stopWords = ['apakah', 'apa', 'bagaimana', 'mengapa', 'dan', 'yang', 'di', 'ke', 'dari', 'adalah', 'ini', 'itu', 'tentang', 'materi', 'kuliah', 'saya', 'tanya', 'jelaskan', 'maksud', 'arti', 'definisi', 'tolong'];
                $words = preg_split('/[\s,\?\.\!]+/', $query);
                $keywords = [];
                foreach ($words as $w) {
                    $w = trim($w);
                    if (strlen($w) > 2 && !in_array($w, $stopWords)) {
                        $keywords[] = $w;
                    }
                }

                // Cari kalimat yang cocok di summary
                $lines = preg_split('/\n+|\.\s+/', $summaryText);
                $matches = [];
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || str_starts_with($line, '---') || str_starts_with($line, '###')) {
                        continue;
                    }
                    
                    $score = 0;
                    foreach ($keywords as $kw) {
                        if (str_contains(strtolower($line), $kw)) {
                            $score++;
                        }
                    }
                    if ($score > 0) {
                        $matches[] = [
                            'line' => $line,
                            'score' => $score
                        ];
                    }
                }

                if (count($matches) > 0) {
                    // Urutkan berdasarkan score tertinggi
                    usort($matches, function($a, $b) {
                        return $b['score'] <=> $a['score'];
                    });

                    // Ambil top 3 kalimat unik
                    $selectedLines = [];
                    foreach ($matches as $m) {
                        $cleanLine = trim($m['line'], "*- \t");
                        if (!in_array($cleanLine, $selectedLines) && strlen($cleanLine) > 10) {
                            $selectedLines[] = $cleanLine;
                            if (count($selectedLines) >= 3) break;
                        }
                    }

                    if (count($selectedLines) > 0) {
                        $responseBody = "Berdasarkan materi kuliah **{$subject}** yang Anda unggah, berikut penjelasan terkait pertanyaan Anda:\n\n";
                        foreach ($selectedLines as $sl) {
                            $responseBody .= "• " . ucfirst($sl) . ".\n";
                        }
                        $responseBody .= "\nApakah penjelasan ini membantu? Anda bisa menanyakan topik detail lainnya dari dokumen ini.";
                        return $personalizationNote . $responseBody;
                    }
                }

                // Fallback jika tidak ada keyword yang cocok, tapi ada summary
                // Berikan ikhtisar singkat dari bab-bab atau poin penting
                preg_match_all('/\* \*\*([^*]+)\*\*/', $summaryText, $terms);
                $termList = array_slice($terms[1] ?? [], 0, 4);
                
                $responseBody = "Pertanyaan Anda tidak secara spesifik terinci di dalam ringkasan dokumen. Namun, dokumen **{$subject}** ini secara umum membahas beberapa konsep kunci:\n\n";
                if (count($termList) > 0) {
                    foreach ($termList as $term) {
                        $responseBody .= "• **" . trim($term) . "**\n";
                    }
                } else {
                    $responseBody .= "• Konsep dasar, paradigma implementasi, dan efisiensi sistem.\n";
                }
                $responseBody .= "\nAda bagian dari materi ini yang ingin Anda bedah lebih spesifik?";
                return $personalizationNote . $responseBody;
            }

            return $personalizationNote . "Berdasarkan materi kuliah **{$subject}** yang Anda unggah, konsep tersebut menjelaskan bahwa optimasi dan logika terstruktur adalah kunci utama. Ada yang ingin didiskusikan secara khusus?";
        }

        return "Response simulasi AI untuk model $model pada fitur $feature.";
    }
}
