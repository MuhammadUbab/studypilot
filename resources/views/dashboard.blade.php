@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header Welcome -->
    <div class="row mb-5 align-items-center">
        <div class="col-md-8">
            <h1 class="display-5 fw-bold mb-1">Halo, {{ auth()->user()->name }}!</h1>
            <p class="text-secondary mb-0">Jurusan {{ auth()->user()->jurusan ?? 'Belum Diatur' }} • Semester {{ auth()->user()->semester ?? 'Belum Diatur' }}</p>
        </div>
        <!-- Gamification Widget: Level, XP, Streak -->
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="glass-card p-3 d-inline-block text-start" style="min-width: 250px;">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold text-heading">Lvl {{ auth()->user()->level }}</span>
                    <span class="text-secondary small">{{ auth()->user()->xp }} / {{ auth()->user()->level * 500 }} XP</span>
                </div>
                <div class="gamification-progress">
                    <div class="gamification-progress-bar" style="width: {{ min(100, (auth()->user()->xp / (max(1, auth()->user()->level) * 500)) * 100) }}%;"></div>
                </div>
                <div class="d-flex justify-content-between mt-3 text-secondary small">
                    <span>🔥 {{ auth()->user()->streak }} Hari Streak</span>
                    <span>🎖️ {{ auth()->user()->level > 5 ? '3' : '1' }} Lencana</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="glass-card stat-card">
                <div class="stat-icon primary"><i class="fa-solid fa-list-check"></i></div>
                <div class="stat-number">{{ $activeTasksCount }}</div>
                <div class="stat-label">Tugas Aktif</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card">
                <div class="stat-icon success"><i class="fa-solid fa-hourglass-half"></i></div>
                <div class="stat-number">{{ $focusMinutesToday }} menit</div>
                <div class="stat-label">Fokus Hari Ini</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card">
                <div class="stat-icon warning"><i class="fa-solid fa-book"></i></div>
                <div class="stat-number">{{ $materialsCount }}</div>
                <div class="stat-label">Materi Diunggah</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card">
                <div class="stat-icon danger"><i class="fa-solid fa-square-poll-vertical"></i></div>
                <div class="stat-number">{{ $avgReadiness }}%</div>
                <div class="stat-label">Kesiapan Ujian (Avg)</div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Row -->
    <div class="row g-4">
        <!-- Analytics & Progress -->
        <div class="col-lg-8">
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-line me-2 text-indigo" style="color:var(--color-primary);"></i>Analitik Produktivitas Belajar</h5>
                <canvas id="productivityChart" height="250"></canvas>
            </div>
            
            <!-- Aktivitas Terbaru -->
            <div class="glass-card p-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-clock-rotate-left me-2 text-indigo" style="color:var(--color-primary);"></i>Aktivitas Terbaru</h5>
                @if($recentActivities->isEmpty())
                    <div class="text-center py-4 text-secondary">
                        <i class="fa-solid fa-folder-open mb-3 fs-1 text-muted"></i>
                        <p class="mb-0">Belum ada aktivitas terbaru. Mulai dengan mengunggah materi atau membuat tugas!</p>
                    </div>
                @else
                    <div class="position-relative ps-4" style="border-left: 2px solid rgba(255, 255, 255, 0.05); margin-left: 10px;">
                        @foreach($recentActivities as $act)
                            <div class="mb-4 position-relative">
                                <span class="position-absolute d-flex align-items-center justify-content-center bg-dark rounded-circle" 
                                      style="left: -33px; top: 0; width: 24px; height: 24px; border: 2px solid var(--border-color);">
                                    <i class="fa-solid {{ $act['icon'] }} {{ $act['color'] }}" style="font-size: 0.75rem;"></i>
                                </span>
                                <div class="ms-2">
                                    <div class="text-heading fw-medium" style="font-size: 0.95rem;">{{ $act['title'] }}</div>
                                    <div class="text-secondary small">{{ $act['time']->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Side: Deadlines & Quick actions -->
        <div class="col-lg-4">
            <!-- Deadline Terdekat -->
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-calendar-day me-2 text-danger"></i>Deadline Terdekat</h5>
                @if($upcomingDeadlines->isEmpty())
                    <div class="text-center py-4 text-secondary">
                        <i class="fa-regular fa-calendar-check mb-3 fs-2 text-muted"></i>
                        <p class="mb-0" style="font-size:0.9rem;">Bagus! Tidak ada tugas dengan tenggat waktu dekat.</p>
                    </div>
                @else
                    <ul class="list-unstyled mb-0">
                        @foreach($upcomingDeadlines as $task)
                            <li class="p-3 mb-2 rounded-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color);">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="text-heading fw-medium text-truncate" style="max-width: 70%;">{{ $task->judul }}</span>
                                    <span class="badge {{ $task->prioritas === 'high' ? 'bg-danger-subtle text-danger' : ($task->prioritas === 'medium' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success') }} text-capitalize">
                                        {{ $task->prioritas }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between text-secondary small">
                                    <span>Tenggat: {{ $task->deadline->translatedFormat('d M Y, H:i') }}</span>
                                    <span class="text-danger fw-semibold">{{ $task->deadline->diffForHumans() }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Quick Action Cards -->
            <div class="glass-card p-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-bolt me-2 text-warning"></i>Aksi Cepat</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('tasks.index') }}" class="btn btn-primary text-start px-3 py-2.5">
                        <i class="fa-solid fa-plus me-2"></i> Tambah Tugas Baru
                    </a>
                    <a href="{{ route('knowledge-hub.index') }}" class="btn btn-outline-light text-start px-3 py-2.5">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i> Unggah Materi Kuliah
                    </a>
                    <a href="{{ route('focus.index') }}" class="btn btn-outline-light text-start px-3 py-2.5">
                        <i class="fa-solid fa-clock me-2"></i> Mulai Pomodoro
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('productivityChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Sesi Fokus (menit)',
                data: {!! json_encode($chartFocusData) !!},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Tugas Selesai',
                data: {!! json_encode($chartTasksCompleted) !!},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#9ca3af'
                    }
                }
            },
            scales: {
                y: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)'
                    },
                    ticks: {
                        color: '#9ca3af',
                        precision: 0
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)'
                    },
                    ticks: {
                        color: '#9ca3af'
                    }
                }
            }
        }
    });
</script>
@endsection
