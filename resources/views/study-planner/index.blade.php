@extends('layouts.app')

@section('title', 'AI Study Planner')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 justify-content-between align-items-center">
        <div class="col-md-6">
            <h1 class="fw-bold text-white"><i class="fa-solid fa-calendar-week text-primary me-2"></i>AI Study Planner</h1>
            <p class="text-secondary mb-0">Rencanakan jadwal belajar otomatis secara cerdas dan efisien berdasarkan daftar tugas dan waktu luang Anda.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            @if(!$sessions->isEmpty())
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <form action="{{ route('study-planner.generate') }}" method="POST" onsubmit="showLoadingPlanner(event)" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fa-solid fa-rotate me-1"></i> Regenerasi AI
                        </button>
                    </form>
                    <button class="btn btn-outline-danger" onclick="confirmResetPlanner()">
                        <i class="fa-solid fa-trash me-1"></i> Bersihkan
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                        <i class="fa-solid fa-plus me-1"></i> Tambah Sesi
                    </button>
                    <a href="{{ route('study-planner.pdf') }}" class="btn btn-outline-light">
                        <i class="fa-solid fa-file-pdf me-1 text-danger"></i> PDF
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Weekly Progress Bar -->
    @if(!$sessions->isEmpty())
        @php
            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            $totalSessions = 0;
            $completedSessions = 0;
            foreach ($hariList as $hari) {
                $daySessions = $sessions->get($hari) ?? collect();
                $totalSessions += $daySessions->count();
                $completedSessions += $daySessions->where('is_completed', true)->count();
            }
            $progressPercent = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100) : 0;
        @endphp
        <div class="glass-card p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-chart-line text-primary me-2"></i>Progres Belajar Mingguan</h5>
                        <span class="badge bg-primary-subtle text-primary fw-bold" style="font-size: 0.9rem;">{{ $progressPercent }}% Selesai</span>
                    </div>
                    <p class="text-secondary small mb-3 mb-md-0">Pencapaian: Anda telah menyelesaikan <strong>{{ $completedSessions }}</strong> dari <strong>{{ $totalSessions }}</strong> sesi belajar yang direncanakan untuk minggu ini.</p>
                </div>
                <div class="col-md-4">
                    <div class="progress" style="height: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 6px; overflow: hidden;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                             style="width: {{ $progressPercent }}%; background: linear-gradient(90deg, var(--color-primary), var(--color-secondary)); border: none;" 
                             aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Empty State -->
    @if($sessions->isEmpty())
        <div class="glass-card p-5 text-center my-5" id="empty-state-planner">
            <div class="py-5 text-secondary">
                <i class="fa-solid fa-calendar-week mb-4 fs-1 text-muted"></i>
                <h4 class="text-white fw-bold">Belum ada rencana belajar aktif</h4>
                <p class="mb-4">Buat rencana belajar harian pertama Anda secara otomatis menggunakan bantuan kecerdasan buatan StudyPilot.</p>
                <form action="{{ route('study-planner.generate') }}" method="POST" id="generate-form" onsubmit="showLoadingPlanner(event)">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg px-4"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Buat Rencana Belajar AI</button>
                </form>
            </div>
        </div>
    @else
        <!-- Rencana Belajar Calendar View -->
        <div class="mb-4">
            <h5 class="fw-bold mb-0 text-white"><i class="fa-solid fa-calendar-alt text-primary me-2"></i>Jadwal Belajar Mingguan</h5>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-5 g-4 mb-5">
            @php
                $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            @endphp
            @foreach($hariList as $hari)
                @php
                    $daySessions = $sessions->get($hari) ?? collect();
                    $daySessions = $daySessions->sortBy('waktu_mulai');
                @endphp
                <div class="col">
                    <div class="glass-card p-3 h-100 d-flex flex-column" style="min-height: 380px;">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary-subtle">
                            <h6 class="fw-bold mb-0 text-white">{{ $hari }}</h6>
                            <span class="badge bg-secondary-subtle text-secondary small">{{ $daySessions->count() }} Sesi</span>
                        </div>

                        <div class="d-flex flex-column gap-3 flex-grow-1">
                            @if($daySessions->isEmpty())
                                <div class="text-center py-5 my-auto text-secondary small">
                                    <i class="fa-solid fa-bed mb-2 text-muted"></i>
                                    <div>Istirahat / Kosong</div>
                                </div>
                            @else
                                @foreach($daySessions as $session)
                                    <div class="p-3 rounded-3 d-flex flex-column gap-2 border border-secondary-subtle transition-all {{ $session->is_completed ? 'bg-success-subtle border-success' : 'bg-dark-subtle' }}" style="background: rgba(255, 255, 255, 0.03); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <!-- Checkbox & Judul -->
                                            <div class="d-flex align-items-start gap-2.5 overflow-hidden">
                                                <input type="checkbox" class="form-check-input mt-1 flex-shrink-0 cursor-pointer" {{ $session->is_completed ? 'checked' : '' }} onclick="toggleSessionComplete({{ $session->id }}, this)">
                                                <span class="fw-semibold text-white text-truncate text-wrap {{ $session->is_completed ? 'text-decoration-line-through text-success' : '' }}" style="font-size: 0.95rem; line-height: 1.4;">
                                                    {{ $session->judul }}
                                                </span>
                                            </div>
                                            <!-- Actions -->
                                            <div class="d-flex gap-1.5 flex-shrink-0 mt-0.5">
                                                <button class="btn btn-link p-0 text-secondary hover-primary" onclick="openEditModal({{ $session }})"><i class="fa-solid fa-edit small"></i></button>
                                                <button class="btn btn-link p-0 text-secondary hover-danger" onclick="confirmDeleteSession({{ $session->id }})"><i class="fa-solid fa-trash small"></i></button>
                                            </div>
                                        </div>

                                        <!-- Waktu & Badge Tugas -->
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-1.5 mt-1 small text-secondary">
                                            <span style="font-size: 0.85rem;"><i class="fa-regular fa-clock me-1 text-primary"></i>{{ $session->waktu_mulai }} - {{ $session->waktu_selesai }}</span>
                                            @if($session->task_id)
                                                <span class="badge bg-indigo-subtle text-primary border border-primary-subtle text-truncate max-w-100" style="padding: 4px 8px; font-size: 0.75rem;" title="{{ $session->task->judul }}">
                                                    <i class="fa-solid fa-list-check me-1"></i> Tugas
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Modal Tambah Sesi -->
<div class="modal fade" id="addSessionModal" tabindex="-1" aria-labelledby="addSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white" style="background-color: var(--bg-sidebar); border: 1px solid var(--border-color); backdrop-filter: var(--glass-blur);">
            <div class="modal-header border-bottom border-secondary-subtle">
                <h5 class="modal-title fw-bold" id="addSessionModalLabel"><i class="fa-solid fa-plus text-primary me-2"></i>Tambah Sesi Belajar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('study-planner.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="judul" class="form-label text-secondary">Judul Sesi</label>
                        <input type="text" class="form-control text-white" style="background-color: var(--input-bg);" id="judul" name="judul" required placeholder="Contoh: Belajar Bab 3 Algoritma">
                    </div>
                    <div class="mb-3">
                        <label for="hari" class="form-label text-secondary">Hari</label>
                        <select class="form-select text-white" style="background-color: var(--input-bg);" id="hari" name="hari" required>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="waktu_mulai" class="form-label text-secondary">Waktu Mulai</label>
                            <input type="time" class="form-control text-white" style="background-color: var(--input-bg);" id="waktu_mulai" name="waktu_mulai" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="waktu_selesai" class="form-label text-secondary">Waktu Selesai</label>
                            <input type="time" class="form-control text-white" style="background-color: var(--input-bg);" id="waktu_selesai" name="waktu_selesai" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="task_id" class="form-label text-secondary">Hubungkan dengan Tugas (Opsional)</label>
                        <select class="form-select text-white" style="background-color: var(--input-bg);" id="task_id" name="task_id">
                            <option value="">-- Tidak Ada --</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}">{{ $task->judul }} ({{ strtoupper($task->prioritas) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary-subtle">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Sesi -->
<div class="modal fade" id="editSessionModal" tabindex="-1" aria-labelledby="editSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white" style="background-color: var(--bg-sidebar); border: 1px solid var(--border-color); backdrop-filter: var(--glass-blur);">
            <div class="modal-header border-bottom border-secondary-subtle">
                <h5 class="modal-title fw-bold" id="editSessionModalLabel"><i class="fa-solid fa-edit text-primary me-2"></i>Edit Sesi Belajar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="edit-session-form">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_judul" class="form-label text-secondary">Judul Sesi</label>
                        <input type="text" class="form-control text-white" style="background-color: var(--input-bg);" id="edit_judul" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_hari" class="form-label text-secondary">Hari</label>
                        <select class="form-select text-white" style="background-color: var(--input-bg);" id="edit_hari" name="hari" required>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="edit_waktu_mulai" class="form-label text-secondary">Waktu Mulai</label>
                            <input type="time" class="form-control text-white" style="background-color: var(--input-bg);" id="edit_waktu_mulai" name="waktu_mulai" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="edit_waktu_selesai" class="form-label text-secondary">Waktu Selesai</label>
                            <input type="time" class="form-control text-white" style="background-color: var(--input-bg);" id="edit_waktu_selesai" name="waktu_selesai" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_task_id" class="form-label text-secondary">Hubungkan dengan Tugas (Opsional)</label>
                        <select class="form-select text-white" style="background-color: var(--input-bg);" id="edit_task_id" name="task_id">
                            <option value="">-- Tidak Ada --</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}">{{ $task->judul }} ({{ strtoupper($task->prioritas) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary-subtle">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Hapus Hidden -->
<form action="" method="POST" id="delete-session-form" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
    function showLoadingPlanner(event) {
        Swal.fire({
            title: 'Membangun Jadwal AI...',
            text: 'StudyPilot sedang menganalisis slot waktu dan tingkat urgensi tugas aktif Anda.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    function confirmResetPlanner() {
        Swal.fire({
            title: 'Bersihkan Semua Jadwal?',
            text: 'Semua rencana belajar mingguan Anda saat ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Bersihkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Buat form post request generator baru untuk menghapus rencana
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('study-planner.generate') }}";
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = "{{ csrf_token() }}";
                
                form.appendChild(csrfInput);
                document.body.appendChild(form);
                
                Swal.fire({
                    title: 'Memproses...',
                    didOpen: () => {
                        Swal.showLoading();
                        form.submit();
                    }
                });
            }
        });
    }

    function toggleSessionComplete(id, checkbox) {
        checkbox.disabled = true;
        
        fetch(`/study-planner/${id}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSR-Token': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            checkbox.disabled = false;
            if (data.success) {
                // Update parent container styling
                const container = checkbox.closest('.transition-all');
                const titleSpan = container.querySelector('.text-truncate');
                
                if (data.is_completed) {
                    container.classList.remove('bg-dark-subtle');
                    container.classList.add('bg-success-subtle', 'border-success');
                    titleSpan.classList.add('text-decoration-line-through', 'text-success');
                } else {
                    container.classList.remove('bg-success-subtle', 'border-success');
                    container.classList.add('bg-dark-subtle');
                    titleSpan.classList.remove('text-decoration-line-through', 'text-success');
                }

                // Perbarui XP dan Level di Sidebar jika data terkirim
                const sidebarXp = document.querySelector('.sidebar-item.active .sidebar-link'); // mock reference
                
                // Show Swal Toast
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: data.is_completed ? 'success' : 'info',
                    title: data.message
                });

                // Reload background stats to keep UI in sync (or redirect shortly)
                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            }
        })
        .catch(err => {
            checkbox.disabled = false;
            console.error(err);
        });
    }

    function openEditModal(session) {
        const form = document.getElementById('edit-session-form');
        form.action = `/study-planner/${session.id}`;
        
        document.getElementById('edit_judul').value = session.judul;
        document.getElementById('edit_hari').value = session.hari;
        document.getElementById('edit_waktu_mulai').value = session.waktu_mulai;
        document.getElementById('edit_waktu_selesai').value = session.waktu_selesai;
        document.getElementById('edit_task_id').value = session.task_id || '';
        
        const modal = new bootstrap.Modal(document.getElementById('editSessionModal'));
        modal.show();
    }

    function confirmDeleteSession(id) {
        Swal.fire({
            title: 'Hapus Sesi Belajar?',
            text: 'Sesi belajar terpilih akan dihapus secara permanen dari jadwal mingguan Anda.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-session-form');
                form.action = `/study-planner/${id}`;
                form.submit();
            }
        });
    }
</script>
@endsection
