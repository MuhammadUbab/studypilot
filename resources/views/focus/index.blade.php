@extends('layouts.app')

@section('title', 'Focus Mode (Pomodoro)')

@section('content')
<div class="container-fluid py-4">
    <div class="row g-4">
        <!-- Main Pomodoro Timer -->
        <div class="col-lg-8">
            <div class="glass-card p-5 text-center position-relative overflow-hidden mb-4">
                <span class="badge bg-indigo-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill mb-4" style="background-color: var(--color-primary-glow);">
                    ⏱️ Pomodoro Focus Timer
                </span>
                
                <!-- Mode Toggles -->
                <div class="d-flex justify-content-center gap-2 mb-5">
                    <button class="btn btn-outline-light active" id="btn-work" onclick="setMode('work')">Fokus (25m)</button>
                    <button class="btn btn-outline-light" id="btn-short" onclick="setMode('short')">Istirahat Pendek (5m)</button>
                    <button class="btn btn-outline-light" id="btn-long" onclick="setMode('long')">Istirahat Panjang (15m)</button>
                </div>

                <!-- Timer Display -->
                <div class="display-1 fw-bold mb-5 text-white" id="main-timer" style="font-family: var(--font-heading); font-size: 6.5rem; letter-spacing: -0.02em;">25:00</div>

                <!-- Controls -->
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-primary btn-lg px-5 py-3 fs-5" id="btn-start" onclick="toggleTimer()">Mulai</button>
                    <button class="btn btn-outline-light btn-lg px-4 py-3" onclick="resetTimer()"><i class="fa-solid fa-rotate-right"></i></button>
                </div>
            </div>

            <!-- Stats Box inside Focus page -->
            <div class="row g-4">
                <div class="col-6">
                    <div class="glass-card p-4 text-center">
                        <h6 class="text-secondary mb-2">Total Sesi Fokus</h6>
                        <h3 class="text-white mb-0 fw-bold">{{ $sessionsCount }}</h3>
                    </div>
                </div>
                <div class="col-6">
                    <div class="glass-card p-4 text-center">
                        <h6 class="text-secondary mb-2">Total Menit Fokus</h6>
                        <h3 class="text-white mb-0 fw-bold">{{ $totalFocusMinutes }} m</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leaderboard Sidebar -->
        <div class="col-lg-4">
            <div class="glass-card p-4 h-100">
                <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-ranking-star text-warning me-2"></i>Leaderboard Mahasiswa</h5>
                
                <div class="d-flex flex-column gap-3">
                    @foreach($leaderboard as $index => $u)
                        <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color);">
                            <div class="d-flex align-items-center gap-3 overflow-hidden">
                                <span class="fw-bold fs-5 text-secondary" style="width:24px; text-align:center;">
                                    @if($index === 0)
                                        🥇
                                    @elseif($index === 1)
                                        🥈
                                    @elseif($index === 2)
                                        🥉
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </span>
                                <img src="{{ $u->foto_profil_url }}" alt="Avatar" class="rounded-circle" style="width:36px; height:36px; object-fit:cover; border: 1px solid rgba(255,255,255,0.1);">
                                <div class="overflow-hidden">
                                    <div class="text-white text-truncate fw-medium" style="font-size:0.9rem;">{{ $u->name }}</div>
                                    <div class="text-secondary small">Lvl {{ $u->level }}</div>
                                </div>
                            </div>
                            <span class="badge bg-indigo-subtle text-primary fw-bold">{{ $u->xp }} XP</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let timeRemaining = 25 * 60;
    let timerInterval = null;
    let isRunning = false;
    let currentMode = 'work';

    function setMode(mode) {
        currentMode = mode;
        
        document.getElementById('btn-work').classList.remove('active');
        document.getElementById('btn-short').classList.remove('active');
        document.getElementById('btn-long').classList.remove('active');
        
        if (mode === 'work') {
            timeRemaining = 25 * 60;
            document.getElementById('btn-work').classList.add('active');
        } else if (mode === 'short') {
            timeRemaining = 5 * 60;
            document.getElementById('btn-short').classList.add('active');
        } else if (mode === 'long') {
            timeRemaining = 15 * 60;
            document.getElementById('btn-long').classList.add('active');
        }
        
        updateDisplay();
        if (isRunning) {
            toggleTimer(); // Pause timer
        }
    }

    function updateDisplay() {
        let minutes = Math.floor(timeRemaining / 60);
        let seconds = timeRemaining % 60;
        document.getElementById('main-timer').innerText = 
            (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
    }

    function toggleTimer() {
        const btn = document.getElementById('btn-start');
        if (isRunning) {
            clearInterval(timerInterval);
            isRunning = false;
            btn.innerText = 'Lanjutkan';
            btn.classList.replace('btn-secondary', 'btn-primary');
        } else {
            isRunning = true;
            btn.innerText = 'Jeda';
            btn.classList.replace('btn-primary', 'btn-secondary');
            
            timerInterval = setInterval(() => {
                if (timeRemaining > 0) {
                    timeRemaining--;
                    updateDisplay();
                } else {
                    clearInterval(timerInterval);
                    isRunning = false;
                    
                    // Selesai Sesi
                    const durationMap = { 'work': 1500, 'short': 300, 'long': 900 };
                    const duration = durationMap[currentMode];
                    
                    // Kirim ke backend untuk log sesi fokus & tambah XP
                    fetch('/focus/complete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSR-Token': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ duration: duration })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Sesi Selesai!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'Lanjutkan',
                                confirmButtonColor: '#6366f1'
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    });

                    resetTimer();
                }
            }, 1000);
        }
    }

    function resetTimer() {
        clearInterval(timerInterval);
        isRunning = false;
        setMode(currentMode);
        const btn = document.getElementById('btn-start');
        btn.innerText = 'Mulai';
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-primary');
    }
</script>
@endsection
