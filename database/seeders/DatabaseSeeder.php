<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PromptSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Admin User
        User::updateOrCreate(
            ['email' => 'admin@studypilot.com'],
            [
                'name' => 'Admin StudyPilot',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'jurusan' => 'Teknologi Informasi',
                'semester' => 8,
                'xp' => 1000,
                'level' => 10,
                'streak' => 12,
                'education_level' => 'guru_dosen',
                'theme_preference' => 'system',
            ]
        );

        // 2. Seed Student User
        User::updateOrCreate(
            ['email' => 'student@studypilot.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('student123'),
                'role' => 'user',
                'jurusan' => 'Teknik Informatika',
                'semester' => 4,
                'xp' => 120,
                'level' => 2,
                'streak' => 3,
                'education_level' => 'mahasiswa',
                'theme_preference' => 'system',
            ]
        );

        // 3. Seed Default Prompts and AI Settings
        $prompts = [
            [
                'key' => 'prompt_summary',
                'value' => "Buat ringkasan komprehensif, terstruktur, dan menarik dari materi kuliah berikut. Sertakan Poin Penting, Kesimpulan, Istilah Penting/Glosarium, dan Catatan per Bab. Gunakan format Markdown yang rapi dengan visualisasi yang menarik jika memungkinkan.",
                'description' => 'Prompt default untuk pembuatan ringkasan materi kuliah.'
            ],
            [
                'key' => 'prompt_quiz',
                'value' => "Buat kuis interaktif berdasarkan materi kuliah berikut dengan format JSON yang valid. Output harus HANYA berupa JSON valid tanpa markdown wrappers atau penjelasan tambahan. Skema JSON harus:
{
  \"questions\": [
    {
      \"question\": \"Teks pertanyaan di sini\",
      \"type\": \"pilihan_ganda | essay | true_false | hots\",
      \"options\": [\"Pilihan A\", \"Pilihan B\", \"Pilihan C\", \"Pilihan D\"],
      \"correct_answer\": \"Jawaban yang benar sesuai isi options, atau True/False untuk tipe true_false\",
      \"explanation\": \"Pembahasan detail mengapa jawaban tersebut benar berdasarkan materi\"
    }
  ]
}",
                'description' => 'Prompt default untuk pembuatan kuis otomatis (pilihan ganda, essay, true/false, HOTS).'
            ],
            [
                'key' => 'prompt_study_planner',
                'value' => "Buat jadwal belajar otomatis harian berdasarkan daftar tugas, deadline, dan waktu luang mahasiswa. Susun prioritas belajar teratur, estimasi waktu pengerjaan, dan alokasi sesi fokus Pomodoro (25 menit fokus, 5 menit istirahat) untuk masing-masing tugas agar mahasiswa terhindar dari burnout.",
                'description' => 'Prompt default untuk pembuatan rencana belajar otomatis.'
            ],
            [
                'key' => 'prompt_exam_predictor',
                'value' => "Analisis materi kisi-kisi atau soal ujian lama berikut ini. Identifikasi pola soal, prediksi 3-5 topik utama yang paling potensial keluar di ujian, serta buat 3 contoh soal latihan prediksi yang relevan lengkap dengan kunci jawaban dan pembahasan strategisnya.",
                'description' => 'Prompt default untuk memprediksi soal dan topik ujian.'
            ],
            [
                'key' => 'prompt_chat_materi',
                'value' => "Anda adalah StudyPilot AI Learning Assistant. Jawab pertanyaan pengguna mengenai materi kuliah ini. Gunakan HANYA informasi dari materi kuliah yang diberikan berikut ini. Jika jawabannya tidak ada di dalam materi kuliah tersebut, jawablah dengan sopan bahwa informasi tidak ditemukan dalam dokumen materi kuliah. Konteks materi: ",
                'description' => 'Prompt default untuk asisten chat materi kuliah.'
            ],
            [
                'key' => 'ai_default_model',
                'value' => "deepseek/deepseek-chat",
                'description' => 'Model AI default yang digunakan di platform StudyPilot (misal: deepseek/deepseek-chat, google/gemini-2.5-flash).'
            ],
        ];

        foreach ($prompts as $prompt) {
            PromptSetting::updateOrCreate(
                ['key' => $prompt['key']],
                [
                    'value' => $prompt['value'],
                    'description' => $prompt['description']
                ]
            );
        }
    }
}
