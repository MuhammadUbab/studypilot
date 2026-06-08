@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header Admin -->
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="display-5 fw-bold mb-1">Admin Panel</h1>
            <p class="text-secondary mb-0">Kelola platform StudyPilot, pantau penggunaan AI, atur prompt, dan analisis statistik langganan.</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="glass-card stat-card">
                <div class="stat-icon primary"><i class="fa-solid fa-users"></i></div>
                <div class="stat-number">{{ $totalUsers }}</div>
                <div class="stat-label">Total Pengguna</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card">
                <div class="stat-icon success"><i class="fa-solid fa-gem"></i></div>
                <div class="stat-number">{{ $premiumUsers }}</div>
                <div class="stat-label">Pengguna Premium</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card">
                <div class="stat-icon warning"><i class="fa-solid fa-brain"></i></div>
                <div class="stat-number">{{ $totalAiRequests }}</div>
                <div class="stat-label">Total AI Requests</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card">
                <div class="stat-icon danger"><i class="fa-solid fa-clock"></i></div>
                <div class="stat-number">{{ $totalFocusHours }} jam</div>
                <div class="stat-label">Total Waktu Fokus</div>
            </div>
        </div>
    </div>

    <!-- Charts & Admin Operations -->
    <div class="row g-4">
        <!-- AI Usage Chart -->
        <div class="col-lg-8">
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-bar me-2 text-indigo" style="color:var(--color-primary);"></i>Tren Penggunaan AI Platform</h5>
                <canvas id="aiUsageChart" height="250"></canvas>
            </div>
        </div>

        <!-- System Status & Fast Config -->
        <div class="col-lg-4">
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-circle-nodes me-2 text-success"></i>Status Sistem & AI</h5>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between mb-3 text-secondary">
                        <span>Database</span>
                        <span class="badge bg-success-subtle text-success">Connected</span>
                    </li>
                    <li class="d-flex justify-content-between mb-3 text-secondary">
                        <span>Default AI Model</span>
                        <span class="badge bg-indigo-subtle text-indigo" style="color:var(--color-primary);">{{ $defaultModel }}</span>
                    </li>
                    <li class="d-flex justify-content-between mb-3 text-secondary">
                        <span>Supabase Storage</span>
                        <span class="badge bg-success-subtle text-success">Active</span>
                    </li>
                    <li class="d-flex justify-content-between mb-0 text-secondary">
                        <span>Sistem Langganan</span>
                        <span class="badge bg-warning-subtle text-warning">Mock Enabled</span>
                    </li>
                </ul>
            </div>

            <!-- Quick Controls -->
            <div class="glass-card p-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-sliders me-2 text-warning"></i>Kontrol Cepat</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.prompts') }}" class="btn btn-primary text-start">
                        <i class="fa-solid fa-terminal me-2"></i> Edit Prompt AI
                    </a>
                    <a href="{{ route('admin.ai-settings') }}" class="btn btn-outline-light text-start">
                        <i class="fa-solid fa-gear me-2"></i> Konfigurasi AI Model
                    </a>
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-light text-start">
                        <i class="fa-solid fa-user-shield me-2"></i> Kelola Pengguna
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('aiUsageChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($aiUsageLabels) !!},
            datasets: [{
                label: 'Jumlah Request AI',
                data: {!! json_encode($aiUsageData) !!},
                backgroundColor: 'rgba(99, 102, 241, 0.6)',
                borderColor: '#6366f1',
                borderWidth: 1
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
                        beginAtZero: true,
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
