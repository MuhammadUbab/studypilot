@extends('layouts.app')

@section('title', 'Analisis Prediksi Ujian')

@section('content')
<div class="container-fluid" style="max-width: 1000px; margin: 0 auto;">
    <!-- Top Nav -->
    <div class="row mb-5 align-items-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('exam-predictor.index') }}" class="text-secondary text-decoration-none">Exam Predictor</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">{{ $prediction->judul }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">{{ $prediction->judul }}</h2>
            <p class="text-secondary mt-1 mb-0">Dianalisis pada {{ $prediction->created_at->translatedFormat('d M Y, H:i') }}</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('exam-predictor.pdf', $prediction->id) }}" class="btn btn-outline-light">
                <i class="fa-solid fa-file-pdf me-2 text-danger"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Layout Grid -->
    <div class="row g-4 mb-5">
        <!-- Readiness Gauge Card -->
        <div class="col-md-4">
            <div class="glass-card p-4 text-center h-100 d-flex flex-column align-items-center justify-content-center">
                <h5 class="fw-bold text-secondary mb-4">Exam Readiness Score</h5>
                
                <!-- Circular progress ring -->
                <div class="position-relative mb-4 d-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                    <!-- SVG ring -->
                    <svg class="position-absolute" style="transform: rotate(-90deg); width: 100%; height: 100%;">
                        <circle cx="75" cy="75" r="65" stroke="rgba(255,255,255,0.03)" stroke-width="12" fill="transparent"/>
                        <circle cx="75" cy="75" r="65" stroke="var(--color-primary)" stroke-width="12" fill="transparent"
                                stroke-dasharray="408.4" 
                                stroke-dashoffset="{{ 408.4 - (408.4 * ($prediction->hasil_prediksi['readiness_score'] ?? 0) / 100) }}"/>
                    </svg>
                    <span class="display-5 fw-bold text-heading" style="font-family: var(--font-heading); z-index: 10;">
                        {{ $prediction->hasil_prediksi['readiness_score'] ?? 0 }}%
                    </span>
                </div>

                <p class="small text-secondary mb-0">
                    Nilai estimasi kesiapan berdasarkan penguasaan silabus Anda saat ini.
                </p>
            </div>
        </div>

        <!-- AI Recommendations Card -->
        <div class="col-md-8">
            <div class="glass-card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-lightbulb text-warning me-2"></i>Rekomendasi Belajar AI</h5>
                <ul class="list-group list-group-flush bg-transparent">
                    @foreach($prediction->hasil_prediksi['recommendations'] ?? [] as $rec)
                        <li class="list-group-item bg-transparent text-light border-secondary-subtle px-0 py-3">
                            <i class="fa-solid fa-circle-notch text-primary me-2"></i> {{ $rec }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Topics & Predictions breakdown -->
    <div class="row g-4">
        <!-- Topics analysis -->
        <div class="col-lg-6">
            <div class="glass-card p-4 h-100">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-list-check text-primary me-2"></i>Topik Ujian & Penguasaan</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-secondary" style="border-bottom: 1px solid rgba(255,255,255,0.08);">
                                <th class="py-2">Nama Topik</th>
                                <th class="py-2">Probabilitas Keluar</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prediction->hasil_prediksi['topics'] ?? [] as $topic)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <td class="py-3 fw-medium">{{ $topic['name'] }}</td>
                                    <td class="py-3">
                                        <span class="badge {{ $topic['probability'] === 'Tinggi' ? 'bg-danger-subtle text-danger' : ($topic['probability'] === 'Sedang' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success') }}">
                                            {{ $topic['probability'] }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge {{ $topic['status'] === 'Dikuasai' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                            {{ $topic['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Predicted questions -->
        <div class="col-lg-6">
            <div class="glass-card p-4 h-100">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-bullseye text-danger me-2"></i>Prediksi Soal Potensial AI</h5>
                <div class="accordion accordion-flush" id="predictionsAccordion">
                    @foreach($prediction->hasil_prediksi['predictions'] ?? [] as $index => $pred)
                        <div class="accordion-item bg-transparent border-bottom border-secondary-subtle">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-transparent text-heading py-3.5 fs-6" type="button" data-bs-toggle="collapse" data-bs-target="#predCollapse-{{ $index }}">
                                    💡 Soal {{ $index + 1 }}: {{ Str::limit($pred['question'], 60) }}
                                </button>
                            </h2>
                            <div id="predCollapse-{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#predictionsAccordion">
                                <div class="accordion-body text-secondary small" style="line-height:1.6;">
                                    <p class="fw-medium mb-2">Soal:</p>
                                    <p class="text-light bg-dark p-3 rounded-3 border border-secondary mb-3">{{ $pred['question'] }}</p>
                                    <p class="text-primary fw-medium mb-2">Jawaban & Pembahasan:</p>
                                    <p class="bg-indigo-subtle p-3 rounded-3" style="background: rgba(99,102,241,0.05); color:#cfd2ff;">{{ $pred['answer'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
