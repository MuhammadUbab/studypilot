@extends('layouts.app')

@section('title', 'AI Study Planner')

@section('content')
<div class="container-fluid">
    <div class="row mb-5 justify-content-between align-items-center">
        <div class="col-md-8">
            <h1 class="fw-bold text-white">AI Study Planner</h1>
            <p class="text-secondary">Rencanakan jadwal belajar otomatis secara cerdas dan efisien berdasarkan daftar tugas dan waktu luang Anda.</p>
        </div>
        <div class="col-md-4 text-md-end" id="download-planner-container" style="display: none;">
            <a href="{{ route('study-planner.pdf') }}" class="btn btn-outline-light">
                <i class="fa-solid fa-file-pdf me-2 text-danger"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Empty State -->
    <div class="glass-card p-4" id="empty-state-planner">
        <div class="text-center py-5 text-secondary">
            <i class="fa-solid fa-calendar-week mb-3 fs-1 text-muted"></i>
            <h5>Belum ada rencana belajar aktif</h5>
            <p class="mb-4">Buat rencana belajar harian pertama Anda secara otomatis menggunakan bantuan kecerdasan buatan StudyPilot.</p>
            <button class="btn btn-primary" onclick="generateAiPlanner()">Buat Rencana Belajar AI</button>
        </div>
    </div>

    <!-- Generated Schedule State -->
    <div class="glass-card p-4" id="schedule-state-planner" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-calendar-alt text-primary me-2"></i>Jadwal Belajar Mingguan AI</h5>
            <button class="btn btn-sm btn-outline-light text-danger" onclick="resetAiPlanner()"><i class="fa-solid fa-trash me-1"></i> Reset</button>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle border border-secondary-subtle">
                <thead>
                    <tr>
                        <th style="width: 20%;">Hari</th>
                        <th style="width: 25%;">Pagi (09:00 - 11:30)</th>
                        <th style="width: 25%;">Siang (13:30 - 15:30)</th>
                        <th style="width: 25%;">Malam (19:00 - 21:00)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold text-white">Senin</td>
                        <td><span class="badge bg-indigo-subtle text-primary">Smart Task Review</span><br><small class="text-secondary">Pengerjaan prioritas utama</small></td>
                        <td><span class="badge bg-secondary-subtle text-secondary">Fokus Mandiri</span><br><small class="text-secondary">Pomodoro 25 menit</small></td>
                        <td><span class="badge bg-success-subtle text-success">AI Notes Review</span><br><small class="text-secondary">Membaca glosarium</small></td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-white">Selasa</td>
                        <td><span class="badge bg-success-subtle text-success">AI Notes Review</span><br><small class="text-secondary">Membaca rangkasan materi</small></td>
                        <td><span class="badge bg-indigo-subtle text-primary">Smart Task Review</span><br><small class="text-secondary">Pengerjaan tugas kuliah</small></td>
                        <td><span class="badge bg-warning-subtle text-warning">Simulasi Kuis AI</span><br><small class="text-secondary">Evaluasi pemahaman</small></td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-white">Rabu</td>
                        <td><span class="badge bg-indigo-subtle text-primary">Smart Task Review</span><br><small class="text-secondary">Menyelesaikan draf laporan</small></td>
                        <td><span class="badge bg-secondary-subtle text-secondary">Fokus Mandiri</span><br><small class="text-secondary">Pomodoro 25 menit</small></td>
                        <td><span class="badge bg-success-subtle text-success">AI Notes Review</span><br><small class="text-secondary">Evaluasi Bab 2</small></td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-white">Kamis</td>
                        <td><span class="badge bg-success-subtle text-success">AI Notes Review</span><br><small class="text-secondary">Kalkulus & Aljabar</small></td>
                        <td><span class="badge bg-indigo-subtle text-primary">Smart Task Review</span><br><small class="text-secondary">Penyempurnaan coding</small></td>
                        <td><span class="badge bg-warning-subtle text-warning">Simulasi Kuis AI</span><br><small class="text-secondary">Evaluasi pemahaman</small></td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-white">Jumat</td>
                        <td><span class="badge bg-indigo-subtle text-primary">Smart Task Review</span><br><small class="text-secondary">Finalisasi & push github</small></td>
                        <td><span class="badge bg-secondary-subtle text-secondary">Fokus Mandiri</span><br><small class="text-secondary">Pomodoro 25 menit</small></td>
                        <td><span class="badge bg-indigo-subtle text-primary">AI Chat Materi</span><br><small class="text-secondary">Tanya jawab RAG</small></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (localStorage.getItem('study_plan_generated') === 'true') {
            document.getElementById('empty-state-planner').style.display = 'none';
            document.getElementById('schedule-state-planner').style.display = 'block';
            document.getElementById('download-planner-container').style.display = 'block';
        }
    });

    function generateAiPlanner() {
        Swal.fire({
            title: 'Membangun Rencana Belajar...',
            text: 'AI sedang menganalisis tugas aktif dan slot waktu luang Anda.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        setTimeout(() => {
            localStorage.setItem('study_plan_generated', 'true');
            Swal.close();
            Swal.fire({
                title: 'Rencana Belajar Selesai!',
                text: 'Jadwal belajar mingguan cerdas Anda berhasil dibuat. Silakan unduh PDF untuk mencetak.',
                icon: 'success',
                confirmButtonColor: '#6366f1'
            }).then(() => {
                document.getElementById('empty-state-planner').style.display = 'none';
                document.getElementById('schedule-state-planner').style.display = 'block';
                document.getElementById('download-planner-container').style.display = 'block';
            });
        }, 1500);
    }

    function resetAiPlanner() {
        Swal.fire({
            title: 'Hapus Rencana Belajar?',
            text: 'Jadwal belajar Anda saat ini akan dihapus dari riwayat.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                localStorage.removeItem('study_plan_generated');
                document.getElementById('empty-state-planner').style.display = 'block';
                document.getElementById('schedule-state-planner').style.display = 'none';
                document.getElementById('download-planner-container').style.display = 'none';
            }
        });
    }
</script>
@endsection
