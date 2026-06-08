@extends('layouts.app')

@section('title', 'Academic Analytics')

@section('content')
<div class="container-fluid">
    <div class="row mb-5 align-items-center">
        <div class="col-md-8">
            <h1 class="fw-bold text-white mb-1">Academic Analytics & Progress Tracker</h1>
            <p class="text-secondary mb-0">Statistik produktivitas, pencapaian kuis, jam belajar, dan visualisasi performa akademik Anda secara lengkap.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('analytics.pdf') }}" class="btn btn-outline-light">
                <i class="fa-solid fa-file-pdf me-2 text-danger"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="glass-card stat-card">
                <div class="stat-icon primary"><i class="fa-solid fa-list-check"></i></div>
                <div class="stat-number">{{ $completedTasks }} / {{ $totalTasks }}</div>
                <div class="stat-label">Tugas Selesai</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card">
                <div class="stat-icon success"><i class="fa-solid fa-hourglass-half"></i></div>
                <div class="stat-number">{{ $totalFocusHours }} jam</div>
                <div class="stat-label">Total Sesi Fokus ({{ $totalFocusSessions }} sesi)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card">
                <div class="stat-icon warning"><i class="fa-solid fa-book"></i></div>
                <div class="stat-number">{{ $totalMaterials }}</div>
                <div class="stat-label">Materi workspace</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card">
                <div class="stat-icon danger"><i class="fa-solid fa-square-poll-vertical"></i></div>
                <div class="stat-number">{{ $avgQuizScore }}%</div>
                <div class="stat-label">Skor Kuis ({{ $quizzesAttempted }} kuis dikerjakan)</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4">
        <!-- Line Chart: Productivity over 14 days -->
        <div class="col-lg-8">
            <div class="glass-card p-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Tren Belajar 14 Hari Terakhir</h5>
                <canvas id="productivityTrendChart" height="280"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart: Task Status Breakdown -->
        <div class="col-lg-4">
            <div class="glass-card p-4 h-100">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-pie me-2 text-warning"></i>Status Tugas Anda</h5>
                <div class="d-flex align-items-center justify-content-center" style="height: 250px;">
                    <canvas id="taskStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Line Chart (Focus Trend & Task Trend)
    const ctxTrend = document.getElementById('productivityTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Fokus (Menit)',
                data: {!! json_encode($chartFocusData) !!},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Tugas Selesai',
                data: {!! json_encode($chartTasksData) !!},
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

    // 2. Doughnut Chart (Task Status)
    const ctxStatus = document.getElementById('taskStatusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($taskStatusLabels) !!},
            datasets: [{
                data: {!! json_encode($taskStatusData) !!},
                backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#9ca3af',
                        padding: 20
                    }
                }
            }
        }
    });
</script>
@endsection
