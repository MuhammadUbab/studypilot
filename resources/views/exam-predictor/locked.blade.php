@extends('layouts.app')

@section('title', 'Exam Predictor Locked')

@section('content')
<div class="container-fluid text-center py-5" style="max-width: 650px; margin: 0 auto;">
    <div class="glass-card p-5">
        <i class="fa-solid fa-lock text-warning display-3 mb-4"></i>
        <h2 class="fw-bold mb-3">AI Exam Predictor & Readiness</h2>
        <p class="text-secondary mb-4" style="line-height: 1.6;">
            Fitur analisis pola ujian UTS/UAS, prediksi soal potensial, dan kalkulasi **Exam Readiness Score** eksklusif hanya untuk pengguna paket **Premium Plus**.
        </p>

        <div class="p-3.5 rounded-3 mb-5 text-start" style="background: rgba(99, 102, 241, 0.08); border-left: 4px solid var(--color-primary);">
            <h6 class="text-white fw-bold mb-1"><i class="fa-solid fa-gem text-warning me-2"></i>Keuntungan Premium Plus:</h6>
            <ul class="list-unstyled mb-0 small text-secondary">
                <li class="mb-1"><i class="fa-solid fa-circle-check text-success me-1.5"></i> Analisis pola soal ujian tahun sebelumnya</li>
                <li class="mb-1"><i class="fa-solid fa-circle-check text-success me-1.5"></i> Prediksi topik utama dan minimal 3 latihan soal potensial</li>
                <li class="mb-1"><i class="fa-solid fa-circle-check text-success me-1.5"></i> Kalkulasi persentase kesiapan ujian (Exam Readiness Score)</li>
            </ul>
        </div>

        <div class="d-grid gap-2">
            <a href="{{ route('subscription.index') }}" class="btn btn-primary btn-lg py-2.5">
                <i class="fa-solid fa-gem me-2"></i> Upgrade ke Premium Plus sekarang
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-light">Kembali ke Dashboard</a>
        </div>
    </div>
</div>
@endsection
