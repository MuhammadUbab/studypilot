@extends('layouts.app')

@section('title', 'Habit Tracker')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 justify-content-between align-items-center">
        <div class="col-md-8">
            <h1 class="fw-bold"><i class="fa-solid fa-circle-check text-primary me-2"></i>Habit Tracker</h1>
            <p class="text-secondary">Bangun kebiasaan belajar yang positif secara disiplin, kumpulkan streak harian, dan dapatkan bonus XP akademik!</p>
        </div>
        <div class="col-md-4 text-md-end">
            <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#addHabitModal">
                <i class="fa-solid fa-plus me-1"></i> Tambah Kebiasaan
            </button>
        </div>
    </div>

    <!-- Stats Summary Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 text-center">
                <span class="text-secondary small d-block mb-1">Kelengkapan Hari Ini</span>
                <h2 class="fw-bold mb-2">{{ $todayCompletionRate }}%</h2>
                <div class="progress gamification-progress" style="height: 10px;">
                    <div class="progress-bar gamification-progress-bar" role="progressbar" style="width: {{ $todayCompletionRate }}%" aria-valuenow="{{ $todayCompletionRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-secondary mt-2 d-block">{{ $completedToday }} dari {{ $totalHabits }} kebiasaan selesai hari ini.</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 text-center d-flex flex-column justify-content-center">
                <span class="text-secondary small d-block mb-1">Streak Kebiasaan Tertinggi</span>
                <h2 class="text-warning fw-bold mb-1"><i class="fa-solid fa-fire me-2"></i>{{ $maxStreak }} Hari</h2>
                <small class="text-secondary">Pertahankan konsistensi belajar Anda setiap hari!</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 text-center d-flex flex-column justify-content-center">
                <span class="text-secondary small d-block mb-1">Total Kebiasaan Aktif</span>
                <h2 class="text-indigo fw-bold mb-1" style="color: var(--color-primary);"><i class="fa-solid fa-clipboard-list me-2"></i>{{ $totalHabits }}</h2>
                <small class="text-secondary">Kebiasaan yang sedang Anda bangun minggu ini.</small>
            </div>
        </div>
    </div>

    <!-- Weekly Progress Chart / Tracker Grid -->
    <div class="glass-card p-4 mb-5">
        <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-line text-primary me-2"></i>Progress Mingguan (Kebiasaan Selesai)</h5>
        <div class="row g-3 text-center justify-content-between">
            @foreach($weeklyProgress as $day)
                <div class="col">
                    <div class="p-3 rounded-4 {{ $day['date'] === date('Y-m-d') ? 'border border-primary bg-primary-subtle' : '' }}" style="background: rgba(255, 255, 255, 0.01);">
                        <span class="text-secondary small d-block mb-2">{{ $day['day_name'] }}</span>
                        <div class="d-flex justify-content-center align-items-center rounded-circle mx-auto mb-2" style="width: 48px; height: 48px; background: rgba(99, 102, 241, 0.08); border: 1px solid var(--border-color);">
                            <span class="fw-bold text-heading">{{ $day['count'] }}</span>
                        </div>
                        <span class="small text-secondary">{{ $day['percentage'] }}%</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Habits List -->
    <div class="glass-card p-4 mb-5">
        <h5 class="fw-bold mb-4"><i class="fa-solid fa-list-check text-primary me-2"></i>Daftar Kebiasaan Belajar</h5>
        
        @if($habits->isEmpty())
            <div class="text-center py-5 text-secondary">
                <i class="fa-solid fa-circle-xmark mb-3 fs-1 text-muted"></i>
                <h5>Belum ada kebiasaan yang dibuat</h5>
                <p class="mb-4">Tentukan target kebiasaan belajar Anda di bawah atau buat kebiasaan baru di atas.</p>
            </div>
        @else
            <div class="d-flex flex-column gap-3">
                @foreach($habits as $habit)
                    @php
                        $isCompleted = $habit->isCompletedToday();
                        $startOfWeek = \Carbon\Carbon::now()->startOfWeek();
                    @endphp
                    <div class="p-3 rounded-4 border border-secondary-subtle d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 transition-all {{ $isCompleted ? 'bg-success-subtle border-success' : 'bg-dark-subtle' }}" style="background: rgba(255, 255, 255, 0.01);">
                        
                        <!-- Checklist & Detail -->
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                    style="width: 40px; height: 40px; border: 2px solid {{ $isCompleted ? 'var(--color-success)' : 'var(--border-color)' }}; background: {{ $isCompleted ? 'var(--color-success)' : 'transparent' }};"
                                    onclick="toggleHabitComplete({{ $habit->id }}, this)">
                                <i class="fa-solid fa-check text-heading {{ $isCompleted ? '' : 'd-none' }}"></i>
                            </button>
                            <div>
                                <h6 class="fw-bold mb-1 {{ $isCompleted ?'text-decoration-line-through text-success' : '' }}">{{ $habit->name }}</h6>
                                <div class="d-flex align-items-center gap-3 text-secondary small">
                                    <span><i class="fa-solid fa-fire text-warning me-1"></i>{{ $habit->streak }} Hari Streak</span>
                                    @if($habit->last_completed_at)
                                        <span><i class="fa-regular fa-clock me-1"></i>Selesai terakhir: {{ $habit->last_completed_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- 7 Days Grid Checklist -->
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            @for($i = 0; $i < 7; $i++)
                                @php
                                    $date = $startOfWeek->copy()->addDays($i);
                                    $done = $habit->logs()->whereDate('completed_date', $date)->exists();
                                    $isToday = $date->isToday();
                                @endphp
                                <div class="text-center">
                                    <span class="text-secondary small d-block mb-1" style="font-size: 0.65rem;">{{ $date->translatedFormat('D') }}</span>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 26px; height: 26px; border: 1.5px solid {{ $done ? 'var(--color-success)' : ($isToday ? 'var(--color-primary)' : 'var(--border-color)') }}; background: {{ $done ? 'rgba(16, 185, 129, 0.2)' : 'transparent' }};"
                                         title="{{ $date->translatedFormat('d M Y') }}">
                                        @if($done)
                                            <i class="fa-solid fa-check text-success small" style="font-size: 0.75rem;"></i>
                                        @elseif($isToday)
                                            <span class="text-indigo small" style="font-size: 0.7rem; color: var(--color-primary);">●</span>
                                        @else
                                            <span class="text-muted small" style="font-size: 0.7rem;"></span>
                                        @endif
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn btn-sm btn-outline-light text-secondary hover-primary" onclick="openEditHabitModal({{ $habit }})"><i class="fa-solid fa-edit me-1"></i> Edit</button>
                            <button class="btn btn-sm btn-outline-light text-secondary hover-danger" onclick="confirmDeleteHabit({{ $habit->id }})"><i class="fa-solid fa-trash me-1"></i> Hapus</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Suggested Habits Section -->
    <div class="glass-card p-4">
        <h5 class="fw-bold mb-4"><i class="fa-solid fa-lightbulb text-warning me-2"></i>Saran Kebiasaan Belajar Positif</h5>
        <div class="row g-3">
            @php
                $suggestions = [
                    ['name' => 'Belajar Mandiri 1 Jam', 'icon' => 'fa-book-open', 'color' => 'text-primary'],
                    ['name' => 'Review Materi Kuliah AI Notes', 'icon' => 'fa-notes-medical', 'color' => 'text-success'],
                    ['name' => 'Mengerjakan Sub-Tugas Harian', 'icon' => 'fa-list-check', 'color' => 'text-info'],
                    ['name' => 'Membaca 1 Jurnal / Artikel Ilmiah', 'icon' => 'fa-file-lines', 'color' => 'text-warning'],
                    ['name' => 'Menyelesaikan 1 Sesi Fokus Pomodoro', 'icon' => 'fa-hourglass-half', 'color' => 'text-danger']
                ];
            @endphp
            @foreach($suggestions as $sug)
                <div class="col-md-6 col-lg-4">
                    <button class="w-100 p-3 rounded-4 border border-secondary-subtle text-start d-flex align-items-center justify-content-between hover-glow cursor-pointer text-heading" 
                            style="background: rgba(255, 255, 255, 0.01);" 
                            onclick="addSuggestedHabit('{{ $sug['name'] }}')">
                        <div class="d-flex align-items-center gap-3 overflow-hidden">
                            <div class="p-2.5 rounded-3 {{ $sug['color'] }} bg-dark-subtle" style="background: rgba(255,255,255,0.02); border:1px solid var(--border-color);">
                                <i class="fa-solid {{ $sug['icon'] }}"></i>
                            </div>
                            <span class="fw-semibold text-truncate" style="font-size: 0.9rem;">{{ $sug['name'] }}</span>
                        </div>
                        <i class="fa-solid fa-plus text-secondary flex-shrink-0"></i>
                    </button>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal Tambah Kebiasaan -->
<div class="modal fade" id="addHabitModal" tabindex="-1" aria-labelledby="addHabitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-heading" style="background-color: var(--bg-sidebar); border: 1px solid var(--border-color); backdrop-filter: var(--glass-blur);">
            <div class="modal-header border-bottom border-secondary-subtle">
                <h5 class="modal-title fw-bold" id="addHabitModalLabel"><i class="fa-solid fa-plus text-primary me-2"></i>Tambah Kebiasaan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('habits.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label text-secondary">Nama Kebiasaan</label>
                        <input type="text" class="form-control text-heading" style="background-color: var(--input-bg);" id="name" name="name" required placeholder="Contoh: Belajar Pemrograman 30 Menit">
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

<!-- Modal Edit Kebiasaan -->
<div class="modal fade" id="editHabitModal" tabindex="-1" aria-labelledby="editHabitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-heading" style="background-color: var(--bg-sidebar); border: 1px solid var(--border-color); backdrop-filter: var(--glass-blur);">
            <div class="modal-header border-bottom border-secondary-subtle">
                <h5 class="modal-title fw-bold" id="editHabitModalLabel"><i class="fa-solid fa-edit text-primary me-2"></i>Edit Kebiasaan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="edit-habit-form">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label text-secondary">Nama Kebiasaan</label>
                        <input type="text" class="form-control text-heading" style="background-color: var(--input-bg);" id="edit_name" name="name" required>
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
<form action="" method="POST" id="delete-habit-form" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
    function toggleHabitComplete(id, button) {
        button.disabled = true;
        
        fetch(`/habits/${id}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSR-Token': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            button.disabled = false;
            if (data.success) {
                const checkIcon = button.querySelector('.fa-check');
                const container = button.closest('.transition-all');
                const titleHeading = container.querySelector('.fw-bold');
                const streakSpan = container.querySelector('.fa-fire').parentElement;

                if (data.is_completed) {
                    button.style.background = 'var(--color-success)';
                    button.style.borderColor = 'var(--color-success)';
                    checkIcon.classList.remove('d-none');
                    container.classList.remove('bg-dark-subtle');
                    container.classList.add('bg-success-subtle', 'border-success');
                    titleHeading.classList.add('text-decoration-line-through', 'text-success');
                } else {
                    button.style.background = 'transparent';
                    button.style.borderColor = 'var(--border-color)';
                    checkIcon.classList.add('d-none');
                    container.classList.remove('bg-success-subtle', 'border-success');
                    container.classList.add('bg-dark-subtle');
                    titleHeading.classList.remove('text-decoration-line-through', 'text-success');
                }

                // Update Streak text
                streakSpan.innerHTML = `<i class="fa-solid fa-fire text-warning me-1"></i>${data.streak} Hari Streak`;

                // Show SweetAlert2 Toast
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

                // Reload background stats to keep UI in sync shortly
                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            }
        })
        .catch(err => {
            button.disabled = false;
            console.error(err);
        });
    }

    function addSuggestedHabit(name) {
        // Buat form post request secara background untuk menambah saran
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('habits.store') }}";
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = "{{ csrf_token() }}";

        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'name';
        nameInput.value = name;

        form.appendChild(csrfInput);
        form.appendChild(nameInput);
        document.body.appendChild(form);
        
        Swal.fire({
            title: 'Memproses...',
            didOpen: () => {
                Swal.showLoading();
                form.submit();
            }
        });
    }

    function openEditHabitModal(habit) {
        const form = document.getElementById('edit-habit-form');
        form.action = `/habits/${habit.id}`;
        
        document.getElementById('edit_name').value = habit.name;
        
        const modal = new bootstrap.Modal(document.getElementById('editHabitModal'));
        modal.show();
    }

    function confirmDeleteHabit(id) {
        Swal.fire({
            title: 'Hapus Kebiasaan Belajar?',
            text: 'Kebiasaan terpilih dan seluruh riwayat progress mingguan Anda akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-habit-form');
                form.action = `/habits/${id}`;
                form.submit();
            }
        });
    }
</script>
@endsection
