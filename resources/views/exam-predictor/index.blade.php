@extends('layouts.app')

@section('title', 'Exam Predictor')

@section('content')
<div class="container-fluid">
    <div class="row mb-5 justify-content-between align-items-center">
        <div class="col-md-8">
            <h1 class="fw-bold">AI Exam Predictor</h1>
            <p class="text-secondary">Analisis pola soal ujian lama dan kisi-kisi kuliah untuk memprediksi topik krusial & soal potensial yang akan keluar.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#runPredictorModal">
                <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Prediksi Ujian Baru
            </button>
        </div>
    </div>

    <!-- Predictions List -->
    @if($predictions->isEmpty())
        <div class="glass-card p-5">
            <div class="text-center py-5 text-secondary">
                <i class="fa-solid fa-graduation-cap mb-3 fs-1 text-muted"></i>
                <h5>Belum ada riwayat prediksi ujian</h5>
                <p class="mb-4">Unggah kisi-kisi atau soal ujian tahun sebelumnya untuk mendapatkan prediksi kesiapan ujian.</p>
                <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#runPredictorModal">Mulai Prediksi Pertama</button>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($predictions as $pred)
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-indigo-subtle text-primary border border-primary-subtle" style="background-color: var(--color-primary-glow);">
                                    Readiness: {{ $pred->hasil_prediksi['readiness_score'] ?? 0 }}%
                                </span>
                                <span class="text-secondary small">{{ $pred->created_at->diffForHumans() }}</span>
                            </div>
                            <h5 class="mb-3 text-truncate">{{ $pred->judul }}</h5>
                            
                            <div class="d-flex gap-4 text-secondary small mb-4">
                                <span><i class="fa-solid fa-list me-1"></i> {{ count($pred->hasil_prediksi['topics'] ?? []) }} Topik</span>
                                <span><i class="fa-solid fa-question me-1"></i> {{ count($pred->hasil_prediksi['predictions'] ?? []) }} Soal Prediksi</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top border-secondary-subtle pt-3 mt-3">
                            <a href="{{ route('exam-predictor.show', $pred->id) }}" class="btn btn-sm btn-primary px-3">
                                <i class="fa-solid fa-eye me-1"></i> Lihat Hasil Analisis
                            </a>
                            <form action="{{ route('exam-predictor.destroy', $pred->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-light text-danger" onclick="confirmDeletePrediction(this)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Modal Prediksi Ujian Baru -->
<div class="modal fade" id="runPredictorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-heading" style="border-radius:16px;">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Analisis Prediksi Ujian AI</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('exam-predictor.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="examTitle" class="form-label">Nama Mata Kuliah / Judul Ujian</label>
                        <input type="text" class="form-control bg-dark border-secondary text-heading" id="examTitle" name="judul" required placeholder="Contoh: UTS Aljabar Linear">
                    </div>
                    
                    <div class="mb-3">
                        <label for="examGuidelines" class="form-label">Silabus / Kisi-Kisi Ujian</label>
                        <textarea class="form-control bg-dark border-secondary text-heading" id="examGuidelines" name="kisi_kisi" rows="4" required placeholder="Tuliskan daftar bab, materi pokok, atau instruksi kisi-kisi dari dosen..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="examOldFile" class="form-label">Upload PDF Soal Ujian Lama (Opsional)</label>
                        <input type="file" class="form-control bg-dark border-secondary text-heading" id="examOldFile" name="soal_lama_file" accept=".pdf">
                        <span class="text-secondary small mt-1 d-block">AI akan mengekstrak soal lama untuk membandingkan pola.</span>
                    </div>

                    <div class="modal-footer border-secondary px-0 pb-0 mt-4">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" onclick="showPredictorLoading(this)">Mulai Analisis AI</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showPredictorLoading(btn) {
        const title = document.getElementById('examTitle').value;
        const guidelines = document.getElementById('examGuidelines').value;

        if (title && guidelines) {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>AI sedang menganalisis pola ujian...';
            btn.setAttribute('disabled', 'disabled');
            btn.form.submit();
        }
    }

    function confirmDeletePrediction(button) {
        const form = button.closest('form');
        Swal.fire({
            title: 'Hapus Prediksi Ujian?',
            text: "Riwayat prediksi ini akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>
@endsection
