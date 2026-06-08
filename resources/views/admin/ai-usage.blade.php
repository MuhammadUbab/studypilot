@extends('layouts.app')

@section('title', 'AI Usage')

@section('content')
<div class="container-fluid">
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="fw-bold">AI Usage Monitoring</h1>
            <p class="text-secondary">Pantau total request AI, konsumsi token API OpenRouter, estimasi biaya, dan riwayat log request AI platform secara langsung.</p>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="glass-card p-4 text-center">
                <h6 class="text-secondary mb-2">Total Requests</h6>
                <h3 class="text-white mb-0 fw-bold">{{ $totalRequests }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 text-center">
                <h6 class="text-secondary mb-2">Total Tokens</h6>
                <h3 class="text-white mb-0 fw-bold">{{ number_format($totalTokens) }} tkn</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 text-center">
                <h6 class="text-secondary mb-2">Estimasi Biaya API</h6>
                <h3 class="text-white mb-0 fw-bold">${{ number_format($estimatedCost, 4) }}</h3>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Model stats table -->
        <div class="col-md-6">
            <div class="glass-card p-4 h-100">
                <h5 class="fw-bold mb-4">Statistik Penggunaan Model</h5>
                @if($modelStats->isEmpty())
                    <p class="text-secondary small">Belum ada statistik model yang tersedia.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-dark table-sm mb-0">
                            <thead>
                                <tr class="text-secondary border-bottom border-secondary-subtle">
                                    <th class="py-2">Model Name</th>
                                    <th class="py-2">Total Requests</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modelStats as $stat)
                                    <tr class="border-bottom border-secondary-subtle">
                                        <td class="py-2.5 text-white">{{ $stat->model }}</td>
                                        <td class="py-2.5 fw-bold">{{ $stat->count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- AI Request History Logs -->
    <div class="glass-card p-4">
        <h5 class="fw-bold mb-4">Log Riwayat Request AI Terbaru</h5>
        @if($recentLogs->isEmpty())
            <div class="text-center py-4 text-secondary">
                <i class="fa-solid fa-receipt mb-3 fs-2 text-muted"></i>
                <p class="mb-0">Belum ada request AI yang terekam.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-secondary border-bottom border-secondary-subtle">
                            <th class="py-2">Pengguna</th>
                            <th class="py-2">Fitur</th>
                            <th class="py-2">Model</th>
                            <th class="py-2">Tokens</th>
                            <th class="py-2 text-end">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLogs as $log)
                            <tr class="border-bottom border-secondary-subtle">
                                <td class="py-3 text-white fw-medium">{{ $log->user->name ?? 'System' }}</td>
                                <td class="py-3 text-capitalize"><span class="badge bg-secondary-subtle text-secondary">{{ str_replace('_', ' ', $log->feature) }}</span></td>
                                <td class="py-3 text-secondary">{{ $log->model }}</td>
                                <td class="py-3 fw-bold">{{ number_format($log->token_usage) }}</td>
                                <td class="py-3 text-secondary small text-end">{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
