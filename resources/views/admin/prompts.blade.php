@extends('layouts.app')

@section('title', 'Prompt Management')

@section('content')
<div class="container-fluid" style="max-width: 900px; margin: 0 auto;">
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="fw-bold">Prompt Management</h1>
            <p class="text-secondary">Ubah sistem instruksi (System Prompt) AI untuk menghasilkan rangkuman materi, kuis, jadwal belajar, dan prediksi ujian secara dinamis.</p>
        </div>
    </div>

    <!-- Prompts List Editor -->
    <div class="glass-card p-4">
        <h5 class="fw-bold mb-4"><i class="fa-solid fa-code me-2 text-indigo" style="color:var(--color-primary);"></i>Editor Sistem Prompt AI</h5>
        <form action="{{ route('admin.prompts.update') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="prompt_summary" class="form-label fw-semibold">1. AI Notes / Summary Prompt</label>
                <textarea class="form-control" id="prompt_summary" name="prompt_summary" rows="4" style="font-family: monospace; font-size:0.9rem;" required>{{ old('prompt_summary', $promptSummary->value ?? '') }}</textarea>
                <span class="text-secondary small mt-1 d-block">Digunakan saat mengekstrak dokumen di Knowledge Hub.</span>
            </div>

            <div class="mb-4">
                <label for="prompt_quiz" class="form-label fw-semibold">2. AI Quiz Generator Prompt</label>
                <textarea class="form-control" id="prompt_quiz" name="prompt_quiz" rows="8" style="font-family: monospace; font-size:0.9rem;" required>{{ old('prompt_quiz', $promptQuiz->value ?? '') }}</textarea>
                <span class="text-secondary small mt-1 d-block">Digunakan saat membuat kuis otomatis dari materi kuliah.</span>
            </div>

            <div class="mb-4">
                <label for="prompt_study_planner" class="form-label fw-semibold">3. AI Study Planner Prompt</label>
                <textarea class="form-control" id="prompt_study_planner" name="prompt_study_planner" rows="4" style="font-family: monospace; font-size:0.9rem;" required>{{ old('prompt_study_planner', $promptStudyPlanner->value ?? '') }}</textarea>
                <span class="text-secondary small mt-1 d-block">Digunakan untuk memformulasikan jadwal belajar harian.</span>
            </div>

            <div class="mb-4">
                <label for="prompt_exam_predictor" class="form-label fw-semibold">4. AI Exam Predictor Prompt</label>
                <textarea class="form-control" id="prompt_exam_predictor" name="prompt_exam_predictor" rows="4" style="font-family: monospace; font-size:0.9rem;" required>{{ old('prompt_exam_predictor', $promptExamPredictor->value ?? '') }}</textarea>
                <span class="text-secondary small mt-1 d-block">Digunakan untuk menganalisis kisi-kisi dan soal ujian lama.</span>
            </div>

            <div class="mb-4">
                <label for="prompt_chat_materi" class="form-label fw-semibold">5. AI Chat Materi Prompt</label>
                <textarea class="form-control" id="prompt_chat_materi" name="prompt_chat_materi" rows="4" style="font-family: monospace; font-size:0.9rem;" required>{{ old('prompt_chat_materi', $promptChatMateri->value ?? '') }}</textarea>
                <span class="text-secondary small mt-1 d-block">Digunakan untuk melayani sesi obrolan/chat di samping dokumen materi.</span>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk me-2"></i> Perbarui Semua Prompt
            </button>
        </form>
    </div>
</div>
@endsection
