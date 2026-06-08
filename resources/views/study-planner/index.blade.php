@extends('layouts.app')

@section('title', 'AI Study Planner')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 justify-content-between align-items-center">
        <div class="col-md-6">
            <h1 class="fw-bold"><i class="fa-solid fa-calendar-week text-primary me-2"></i>AI Study Planner</h1>
            <p class="text-secondary mb-0">Rencanakan jadwal belajar otomatis secara cerdas dan efisien berdasarkan daftar tugas dan waktu luang Anda.</p>
        </div>
    </div>

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

    <!-- Progres & Statistik Mingguan -->
    <div class="row g-4 mb-4">
        <!-- Progress Mingguan Card -->
        <div class="col-md-6">
            <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-secondary text-uppercase small tracking-wider">Progres Belajar Mingguan</span>
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1" style="font-size: 0.85rem;">{{ $progressPercent }}% Selesai</span>
                    </div>
                    <h3 class="fw-bold mb-1">{{ $progressPercent }}%</h3>
                    <p class="text-secondary small mb-3">Persentase penyelesaian seluruh sesi belajar yang direncanakan minggu ini.</p>
                </div>
                <div class="progress" style="height: 10px; background: rgba(255, 255, 255, 0.05); border-radius: 6px; overflow: hidden;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                         style="width: {{ $progressPercent }}%; background: linear-gradient(90deg, var(--color-primary), var(--color-secondary)); border: none;" 
                         aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
        
        <!-- Statistik Sesi Selesai Card -->
        <div class="col-md-6">
            <div class="glass-card p-4 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <span class="fw-bold text-secondary text-uppercase small tracking-wider">Statistik Sesi Belajar</span>
                    <h3 class="fw-bold mt-2 mb-1">{{ $completedSessions }} <span class="text-secondary fs-5 fw-normal">/ {{ $totalSessions }} Sesi Selesai</span></h3>
                    <p class="text-secondary small mb-0">
                        @if($totalSessions > 0)
                            Anda telah menyelesaikan {{ $completedSessions }} dari total {{ $totalSessions }} sesi belajar minggu ini. Tetap semangat!
                        @else
                            Belum ada sesi belajar yang direncanakan. Buat dengan AI atau tambah manual.
                        @endif
                    </p>
                </div>
                <div class="stat-icon success" style="width: 56px; height: 56px; border-radius: 14px; background: rgba(16, 185, 129, 0.1); color: var(--color-success); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Unified Toolbar -->
    <div class="glass-card p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="fs-6 fw-bold"><i class="fa-solid fa-calendar-days text-primary me-2"></i>Jadwal Kerja & Belajar</span>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form action="{{ route('study-planner.generate') }}" method="POST" onsubmit="showLoadingPlanner(event)" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Generate AI
                </button>
            </form>
            <button class="btn btn-outline-danger btn-sm" onclick="confirmResetPlanner()" {{ $sessions->isEmpty() ? 'disabled' : '' }}>
                <i class="fa-solid fa-trash me-1"></i> Bersihkan
            </button>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                <i class="fa-solid fa-plus me-1"></i> Tambah Sesi
            </button>
            <a href="{{ route('study-planner.pdf') }}" class="btn btn-outline-light btn-sm {{ $sessions->isEmpty() ? 'disabled' : '' }}" {{ $sessions->isEmpty() ? 'style=pointer-events:none;opacity:0.6;' : '' }}>
                <i class="fa-solid fa-file-pdf me-1 text-danger"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Empty State -->
    @if($sessions->isEmpty())
        <div class="glass-card p-5 text-center my-5" id="empty-state-planner">
            <div class="py-5 text-secondary">
                <i class="fa-solid fa-calendar-week mb-4 fs-1 text-muted"></i>
                <h4 class="fw-bold">Belum ada rencana belajar aktif</h4>
                <p class="mb-4">Buat rencana belajar harian pertama Anda secara otomatis menggunakan bantuan kecerdasan buatan StudyPilot.</p>
                <form action="{{ route('study-planner.generate') }}" method="POST" id="generate-form" onsubmit="showLoadingPlanner(event)">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg px-4"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Buat Rencana Belajar AI</button>
                </form>
            </div>
        </div>
    @else
        <!-- Rencana Belajar Calendar Grid View (Senin - Minggu) -->
        <div class="planner-grid">
            @foreach($hariList as $hari)
                @php
                    $daySessions = $sessions->get($hari) ?? collect();
                    $daySessions = $daySessions->sortBy('waktu_mulai');
                @endphp
                <div class="planner-col">
                    <div class="planner-col-header">
                        <h6 class="planner-col-title">{{ $hari }}</h6>
                        <span class="planner-col-badge">{{ $daySessions->count() }} Sesi</span>
                    </div>

                    <div class="d-flex flex-column gap-3 flex-grow-1">
                        @if($daySessions->isEmpty())
                            <div class="text-center py-5 my-auto text-secondary small">
                                <i class="fa-solid fa-bed mb-2 text-muted fs-4"></i>
                                <div>Istirahat / Kosong</div>
                            </div>
                        @else
                            @foreach($daySessions as $session)
                                <div class="session-card {{ $session->is_completed ? 'completed' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <!-- Checkbox & Judul -->
                                        <div class="d-flex align-items-start gap-2.5 overflow-hidden">
                                            <input type="checkbox" class="form-check-input mt-1 flex-shrink-0 cursor-pointer" {{ $session->is_completed ? 'checked' : '' }} onclick="toggleSessionComplete({{ $session->id }}, this)" style="width: 17px; height: 17px;">
                                            <span class="session-card-title text-wrap {{ $session->is_completed ? 'text-decoration-line-through text-success' : '' }}">
                                                {{ $session->judul }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Waktu & Badge Tugas & Aksi -->
                                    <div class="d-flex align-items-center justify-content-between mt-3">
                                        <span class="session-card-time">
                                            <i class="fa-regular fa-clock text-primary"></i>{{ $session->waktu_mulai }} - {{ $session->waktu_selesai }}
                                        </span>
                                        <div class="d-flex gap-2 align-items-center session-card-actions">
                                            @if($session->task_id)
                                                <span class="badge bg-indigo-subtle text-primary border border-primary-subtle text-truncate max-w-100" style="padding: 4px 6px; font-size: 0.7rem;" title="{{ $session->task->judul }}">
                                                    <i class="fa-solid fa-list-check me-0.5"></i> Tugas
                                                </span>
                                            @endif
                                            <button type="button" class="btn-edit border-0 bg-transparent" onclick="openEditModal({{ $session }})" title="Edit"><i class="fa-solid fa-pencil" style="font-size: 0.8rem;"></i></button>
                                            <button type="button" class="btn-delete border-0 bg-transparent" onclick="confirmDeleteSession({{ $session->id }})" title="Hapus"><i class="fa-solid fa-trash-can" style="font-size: 0.8rem;"></i></button>
                                        </div>
                                    </div>
                                </div>
                              @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Modal Tambah Sesi -->
<div class="modal fade" id="addSessionModal" tabindex="-1" aria-labelledby="addSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: var(--bg-sidebar); border: 1px solid var(--border-color); color: var(--text-primary); backdrop-filter: var(--glass-blur);">
            <div class="modal-header border-bottom border-secondary-subtle">
                <h5 class="modal-title fw-bold" id="addSessionModalLabel"><i class="fa-solid fa-plus text-primary me-2"></i>Tambah Sesi Belajar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('study-planner.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Sesi</label>
                        <input type="text" class="form-control" style="background-color: var(--input-bg); color: var(--text-primary);" id="judul" name="judul" required placeholder="Contoh: Belajar Bab 3 Algoritma">
                    </div>
                    <div class="mb-3">
                        <label for="hari" class="form-label">Hari</label>
                        <select class="form-select" style="background-color: var(--input-bg); color: var(--text-primary);" id="hari" name="hari" required>
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
                            <label for="waktu_mulai" class="form-label">Waktu Mulai</label>
                            <input type="time" class="form-control" style="background-color: var(--input-bg); color: var(--text-primary);" id="waktu_mulai" name="waktu_mulai" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="waktu_selesai" class="form-label">Waktu Selesai</label>
                            <input type="time" class="form-control" style="background-color: var(--input-bg); color: var(--text-primary);" id="waktu_selesai" name="waktu_selesai" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="task_id" class="form-label">Hubungkan dengan Tugas (Opsional)</label>
                        <select class="form-select" style="background-color: var(--input-bg); color: var(--text-primary);" id="task_id" name="task_id">
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
        <div class="modal-content" style="background-color: var(--bg-sidebar); border: 1px solid var(--border-color); color: var(--text-primary); backdrop-filter: var(--glass-blur);">
            <div class="modal-header border-bottom border-secondary-subtle">
                <h5 class="modal-title fw-bold" id="editSessionModalLabel"><i class="fa-solid fa-edit text-primary me-2"></i>Edit Sesi Belajar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="edit-session-form">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_judul" class="form-label">Judul Sesi</label>
                        <input type="text" class="form-control" style="background-color: var(--input-bg); color: var(--text-primary);" id="edit_judul" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_hari" class="form-label">Hari</label>
                        <select class="form-select" style="background-color: var(--input-bg); color: var(--text-primary);" id="edit_hari" name="hari" required>
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
                            <label for="edit_waktu_mulai" class="form-label">Waktu Mulai</label>
                            <input type="time" class="form-control" style="background-color: var(--input-bg); color: var(--text-primary);" id="edit_waktu_mulai" name="waktu_mulai" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="edit_waktu_selesai" class="form-label">Waktu Selesai</label>
                            <input type="time" class="form-control" style="background-color: var(--input-bg); color: var(--text-primary);" id="edit_waktu_selesai" name="waktu_selesai" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_task_id" class="form-label">Hubungkan dengan Tugas (Opsional)</label>
                        <select class="form-select" style="background-color: var(--input-bg); color: var(--text-primary);" id="edit_task_id" name="task_id">
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
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('study-planner.clear') }}";
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = "{{ csrf_token() }}";
                form.appendChild(csrfInput);

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
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
                const container = checkbox.closest('.session-card');
                const titleSpan = container.querySelector('.session-card-title');
                
                if (data.is_completed) {
                    container.classList.add('completed');
                    titleSpan.classList.add('text-decoration-line-through', 'text-success');
                } else {
                    container.classList.remove('completed');
                    titleSpan.classList.remove('text-decoration-line-through', 'text-success');
                }

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
