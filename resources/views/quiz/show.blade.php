@extends('layouts.app')

@section('title', $quiz->judul_quiz)

@section('content')
<div class="container-fluid" style="max-width: 900px; margin: 0 auto;">
    <!-- Top Nav -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('knowledge-hub.index') }}" class="text-secondary text-decoration-none">Knowledge Hub</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('knowledge-hub.show', $quiz->material_id) }}" class="text-secondary text-decoration-none">{{ $quiz->material->judul }}</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Kuis AI</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">{{ $quiz->judul_quiz }}</h2>
        </div>
    </div>

    <!-- Quiz Attempt Mode -->
    @if($quiz->skor === null)
        <form action="{{ route('quizzes.submit', $quiz->id) }}" method="POST">
            @csrf
            
            @php $questions = $quiz->soal_jawaban['questions'] ?? []; @endphp
            @foreach($questions as $index => $q)
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-indigo-subtle text-primary text-uppercase" style="background-color: var(--color-primary-glow);">
                            Soal {{ $index + 1 }}
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary text-uppercase small">
                            Tipe: {{ str_replace('_', ' ', $q['type']) }}
                        </span>
                    </div>

                    <h5 class="mb-4" style="line-height: 1.5;">{{ $q['question'] }}</h5>

                    <!-- Render Options based on Type -->
                    @if(in_array($q['type'], ['pilihan_ganda', 'hots', 'true_false']))
                        <div class="d-flex flex-column gap-2.5">
                            @foreach($q['options'] as $optIdx => $option)
                                <label class="p-3 rounded-3 border d-flex align-items-center gap-3 cursor-pointer option-label" 
                                       style="background: rgba(255,255,255,0.02); border-color: var(--border-color); transition: all 0.2s;" 
                                       id="option-label-{{ $index }}-{{ $optIdx }}">
                                    <input type="radio" name="answers[{{ $index }}]" value="{{ $option }}" required 
                                           onclick="selectOption({{ $index }}, {{ $optIdx }}, {{ count($q['options']) }})">
                                    <span class="text-light">{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <!-- Essay -->
                        <div class="mb-2">
                            <label class="form-label text-secondary">Tulis jawaban Anda di sini:</label>
                            <textarea class="form-control" name="answers[{{ $index }}]" rows="3" required placeholder="Tulis jawaban komprehensif Anda..."></textarea>
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="d-flex justify-content-end mb-5">
                <button type="submit" class="btn btn-primary btn-lg px-5 py-3">Submit Kuis & Hitung Nilai</button>
            </div>
        </form>
    @else
        <!-- Quiz Score / Evaluation Mode -->
        <div class="glass-card p-5 mb-5 text-center position-relative overflow-hidden">
            <div class="position-absolute" style="top:-50px; right:-50px; width:200px; height:200px; background:radial-gradient(circle, var(--color-primary-glow) 0%, transparent 70%); border-radius:50%; pointer-events:none;"></div>
            
            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill mb-3">Evaluasi AI Selesai</span>
            <h1 class="display-3 fw-bold mb-2" style="font-family: var(--font-heading);">{{ $quiz->skor }} <span class="fs-4 text-secondary">/ 100</span></h1>
            
            @if($quiz->skor >= 70)
                <p class="text-success fw-semibold fs-5 mb-4">🎉 Luar biasa! Anda lulus kuis ini dengan sangat baik.</p>
            @else
                <p class="text-warning fw-semibold fs-5 mb-4">💪 Bagus! Tetap semangat, tinjau pembahasan di bawah untuk belajar lagi.</p>
            @endif

            <div class="d-flex justify-content-center gap-3">
                <form action="{{ route('quizzes.generate', $quiz->material_id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">Ulangi Kuis</button>
                </form>
                <a href="{{ route('quizzes.pdf', $quiz->id) }}" class="btn btn-outline-light">
                    <i class="fa-solid fa-file-pdf me-2 text-danger"></i> Download PDF
                </a>
                <a href="{{ route('knowledge-hub.show', $quiz->material_id) }}" class="btn btn-outline-light">Kembali ke Materi</a>
            </div>
        </div>

        <h3 class="fw-bold mb-4">Pembahasan Soal AI</h3>
        
        @php $questions = $quiz->soal_jawaban['questions'] ?? []; @endphp
        @foreach($questions as $index => $q)
            <div class="glass-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-secondary-subtle text-secondary text-uppercase">
                        Soal {{ $index + 1 }}
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary text-uppercase small">
                        Tipe: {{ str_replace('_', ' ', $q['type']) }}
                    </span>
                </div>

                <h5 class="mb-4">{{ $q['question'] }}</h5>

                @if(in_array($q['type'], ['pilihan_ganda', 'hots', 'true_false']))
                    <div class="d-flex flex-column gap-2 mb-4">
                        @foreach($q['options'] as $option)
                            @php 
                                $isCorrect = strtolower($option) === strtolower($q['correct_answer']);
                                $borderStyle = 'border-color: var(--border-color);';
                                $bgStyle = 'background: rgba(255,255,255,0.01);';
                                $icon = '';

                                if ($isCorrect) {
                                    $borderStyle = 'border-color: var(--color-success) !important;';
                                    $bgStyle = 'background: rgba(16, 185, 129, 0.08) !important;';
                                    $icon = '<i class="fa-solid fa-circle-check text-success me-2"></i>';
                                }
                            @endphp
                            <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between" 
                                 style="{{ $borderStyle }} {{ $bgStyle }}">
                                <span class="text-light">{!! $icon !!} {{ $option }}</span>
                                @if($isCorrect)
                                    <span class="text-success small fw-semibold">Kunci Jawaban</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Essay Answer -->
                    <div class="p-3 rounded-3 border border-secondary mb-4" style="background: rgba(255,255,255,0.01);">
                        <span class="text-secondary small d-block mb-1">Rekomendasi Jawaban AI:</span>
                        <p class="text-light mb-0">{{ $q['correct_answer'] }}</p>
                    </div>
                @endif

                <!-- AI Explanation -->
                <div class="p-3.5 rounded-3" style="background: rgba(99, 102, 241, 0.08); border-left: 4px solid var(--color-primary);">
                    <span class="fw-semibold text-primary d-block small mb-1">Pembahasan AI:</span>
                    <p class="text-secondary small mb-0">{{ $q['explanation'] }}</p>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection

@section('styles')
<style>
    .cursor-pointer {
        cursor: pointer;
    }
    .option-label:hover {
        background: rgba(255, 255, 255, 0.06) !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
    }
</style>
@endsection

@section('scripts')
<script>
    function selectOption(qIdx, optIdx, totalOpts) {
        // Reset all labels for this question
        for (let i = 0; i < totalOpts; i++) {
            const label = document.getElementById(`option-label-${qIdx}-${i}`);
            if (label) {
                label.style.borderColor = 'var(--border-color)';
                label.style.background = 'rgba(255,255,255,0.02)';
            }
        }
        
        // Highlight selected
        const selectedLabel = document.getElementById(`option-label-${qIdx}-${optIdx}`);
        if (selectedLabel) {
            selectedLabel.style.borderColor = 'var(--color-primary)';
            selectedLabel.style.background = 'rgba(99,102,241,0.08)';
        }
    }
</script>
@endsection
