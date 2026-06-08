<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - StudyPilot</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Dark Mode CSS -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    
    <!-- Theme loading script to prevent flash -->
    <script>
        (function() {
            const dbTheme = "{{ auth()->check() ? auth()->user()->theme_preference : 'system' }}";
            localStorage.setItem('theme_preference', dbTheme);
            let themeToApply = dbTheme;
            if (dbTheme === 'system') {
                themeToApply = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', themeToApply);
        })();

        // Listen for OS changes in system mode
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            const currentPref = localStorage.getItem('theme_preference') || 'system';
            if (currentPref === 'system') {
                document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            }
        });
    </script>
    @yield('styles')
</head>
<body>

    <!-- Mobile Top Navigation -->
    <header class="d-lg-none border-bottom p-3 d-flex justify-content-between align-items-center w-100" style="background-color: var(--bg-sidebar); border-color: var(--border-color); position: sticky; top: 0; z-index: 1000;">
        <a href="{{ route('landing') }}" class="text-decoration-none fs-4 fw-bold text-heading mb-0">
            <i class="fa-solid fa-paper-plane me-2 text-indigo" style="color: var(--color-primary);"></i>StudyPilot
        </a>
        <div class="d-flex align-items-center gap-2">
            <!-- Theme toggle in header -->
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-light py-1 px-2 theme-toggle-btn" data-theme-val="light" onclick="setGlobalTheme('light')"><i class="fa-solid fa-sun"></i></button>
                <button type="button" class="btn btn-outline-light py-1 px-2 theme-toggle-btn" data-theme-val="dark" onclick="setGlobalTheme('dark')"><i class="fa-solid fa-moon"></i></button>
                <button type="button" class="btn btn-outline-light py-1 px-2 theme-toggle-btn" data-theme-val="system" onclick="setGlobalTheme('system')"><i class="fa-solid fa-desktop"></i></button>
            </div>
            <button class="btn btn-outline-light btn-sm px-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                <i class="fa-solid fa-bars fs-5"></i>
            </button>
        </div>
    </header>

    <!-- Mobile Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start text-heading d-lg-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel" style="background-color: var(--bg-sidebar); width: 280px; border-right: 1px solid var(--border-color);">
        <div class="offcanvas-header border-bottom" style="border-color: var(--border-color);">
            <h5 class="offcanvas-title fw-bold" id="mobileSidebarLabel">
                <i class="fa-solid fa-paper-plane me-2 text-indigo" style="color: var(--color-primary);"></i>StudyPilot
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column justify-content-between">
            <div class="sidebar-scrollable flex-grow-1 overflow-y-auto w-100">
                <div class="p-3">
                    <ul class="sidebar-menu p-0" style="list-style: none;">
                        @if(auth()->user()->role === 'admin')
                            <!-- Admin Sidebar -->
                            <li class="sidebar-item mb-2 {{ request()->is('admin') ? 'active' : '' }}">
                                <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                                    <i class="fa-solid fa-chart-line me-3"></i>Overview
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('admin/users') ? 'active' : '' }}">
                                <a href="{{ route('admin.users') }}" class="sidebar-link">
                                    <i class="fa-solid fa-users me-3"></i>User Management
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('admin/subscriptions') ? 'active' : '' }}">
                                <a href="{{ route('admin.subscriptions') }}" class="sidebar-link">
                                    <i class="fa-solid fa-credit-card me-3"></i>Subscriptions
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('admin/ai-usage') ? 'active' : '' }}">
                                <a href="{{ route('admin.ai-usage') }}" class="sidebar-link">
                                    <i class="fa-solid fa-brain me-3"></i>AI Usage
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('admin/prompts') ? 'active' : '' }}">
                                <a href="{{ route('admin.prompts') }}" class="sidebar-link">
                                    <i class="fa-solid fa-terminal me-3"></i>Prompts
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('admin/ai-settings') ? 'active' : '' }}">
                                <a href="{{ route('admin.ai-settings') }}" class="sidebar-link">
                                    <i class="fa-solid fa-sliders me-3"></i>AI Settings
                                </a>
                            </li>
                        @else
                            <!-- User/Student Sidebar -->
                            <li class="sidebar-item mb-2 {{ request()->is('dashboard') ? 'active' : '' }}">
                                <a href="{{ route('dashboard') }}" class="sidebar-link">
                                    <i class="fa-solid fa-gauge-high me-3"></i>Dashboard
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('tasks*') ? 'active' : '' }}">
                                <a href="{{ route('tasks.index') }}" class="sidebar-link">
                                    <i class="fa-solid fa-list-check me-3"></i>Smart Tasks
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('study-planner*') ? 'active' : '' }}">
                                <a href="{{ route('study-planner.index') }}" class="sidebar-link">
                                    <i class="fa-solid fa-calendar-week me-3"></i>Study Planner
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('knowledge-hub*') ? 'active' : '' }}">
                                <a href="{{ route('knowledge-hub.index') }}" class="sidebar-link">
                                    <i class="fa-solid fa-book-open me-3"></i>Knowledge Hub
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('exam-predictor*') ? 'active' : '' }}">
                                <a href="{{ route('exam-predictor.index') }}" class="sidebar-link">
                                    <i class="fa-solid fa-wand-magic-sparkles me-3"></i>Exam Predictor
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('focus*') ? 'active' : '' }}">
                                <a href="{{ route('focus.index') }}" class="sidebar-link">
                                    <i class="fa-solid fa-clock me-3"></i>Focus Mode
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('habits*') ? 'active' : '' }}">
                                <a href="{{ route('habits.index') }}" class="sidebar-link">
                                    <i class="fa-solid fa-circle-check me-3"></i>Habit Tracker
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('analytics*') ? 'active' : '' }}">
                                <a href="{{ route('analytics.index') }}" class="sidebar-link">
                                    <i class="fa-solid fa-chart-pie me-3"></i>Analytics
                                </a>
                            </li>
                            <li class="sidebar-item mb-2 {{ request()->is('subscription*') ? 'active' : '' }}">
                                <a href="{{ route('subscription.index') }}" class="sidebar-link">
                                    <i class="fa-solid fa-gem me-3"></i>Upgrade Premium
                                </a>
                            </li>
                        @endif
                        
                        <li class="sidebar-item mb-2 {{ request()->is('profile*') ? 'active' : '' }}">
                            <a href="{{ route('profile.edit') }}" class="sidebar-link">
                                <i class="fa-solid fa-user-gear me-3"></i>Pengaturan Profil
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Mobile Focus Timer Widget -->
                @if(auth()->user()->role !== 'admin')
                <div class="sidebar-focus-widget mx-3 mb-3">
                    <div class="focus-widget-title">Focus Session</div>
                    <div class="focus-widget-timer text-center widget-timer-display">25:00</div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-grow-1 widget-start-btn" onclick="toggleWidgetTimer()">Mulai</button>
                        <button class="btn btn-outline-light btn-sm widget-reset-btn" onclick="resetWidgetTimer()"><i class="fa-solid fa-rotate-right"></i></button>
                    </div>
                </div>
                @endif
                <!-- Mobile Theme Toggle Widget -->
                <div class="px-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center p-2.5 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color);">
                        <span class="small text-secondary fw-semibold"><i class="fa-solid fa-circle-half-stroke me-2"></i>Tema</span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-light py-0.5 px-2 theme-toggle-btn" data-theme-val="light" onclick="setGlobalTheme('light')"><i class="fa-solid fa-sun"></i></button>
                            <button type="button" class="btn btn-outline-light py-0.5 px-2 theme-toggle-btn" data-theme-val="dark" onclick="setGlobalTheme('dark')"><i class="fa-solid fa-moon"></i></button>
                            <button type="button" class="btn btn-outline-light py-0.5 px-2 theme-toggle-btn" data-theme-val="system" onclick="setGlobalTheme('system')"><i class="fa-solid fa-desktop"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="user-card-sm border-top" style="border-color: var(--border-color);">
                <img src="{{ auth()->user()->foto_profil_url }}" alt="Foto Profil" class="user-avatar-sm">
                <div class="overflow-hidden flex-grow-1">
                    <div class="user-name-sm text-truncate">{{ auth()->user()->name }}</div>
                    <div class="user-role-sm text-capitalize">{{ auth()->user()->role === 'admin' ? 'Administrator' : 'Mahasiswa' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <a href="{{ route('landing') }}" class="sidebar-brand text-decoration-none">
                <i class="fa-solid fa-paper-plane me-2 text-indigo"></i>StudyPilot
            </a>
            
            <div class="sidebar-scrollable flex-grow-1 overflow-y-auto px-0 w-100">
                <ul class="sidebar-menu">
                    @if(auth()->user()->role === 'admin')
                        <!-- Admin Sidebar -->
                        <li class="sidebar-item {{ request()->is('admin') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                                <i class="fa-solid fa-chart-line"></i>Overview
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('admin/users') ? 'active' : '' }}">
                            <a href="{{ route('admin.users') }}" class="sidebar-link">
                                <i class="fa-solid fa-users"></i>User Management
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('admin/subscriptions') ? 'active' : '' }}">
                            <a href="{{ route('admin.subscriptions') }}" class="sidebar-link">
                                <i class="fa-solid fa-credit-card"></i>Subscriptions
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('admin/ai-usage') ? 'active' : '' }}">
                            <a href="{{ route('admin.ai-usage') }}" class="sidebar-link">
                                <i class="fa-solid fa-brain"></i>AI Usage
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('admin/prompts') ? 'active' : '' }}">
                            <a href="{{ route('admin.prompts') }}" class="sidebar-link">
                                <i class="fa-solid fa-terminal"></i>Prompts
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('admin/ai-settings') ? 'active' : '' }}">
                            <a href="{{ route('admin.ai-settings') }}" class="sidebar-link">
                                <i class="fa-solid fa-sliders"></i>AI Settings
                            </a>
                        </li>
                    @else
                        <!-- User/Student Sidebar -->
                        <li class="sidebar-item {{ request()->is('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}" class="sidebar-link">
                                <i class="fa-solid fa-gauge-high"></i>Dashboard
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('tasks*') ? 'active' : '' }}">
                            <a href="{{ route('tasks.index') }}" class="sidebar-link">
                                <i class="fa-solid fa-list-check"></i>Smart Tasks
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('study-planner*') ? 'active' : '' }}">
                            <a href="{{ route('study-planner.index') }}" class="sidebar-link">
                                <i class="fa-solid fa-calendar-week"></i>Study Planner
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('knowledge-hub*') ? 'active' : '' }}">
                            <a href="{{ route('knowledge-hub.index') }}" class="sidebar-link">
                                <i class="fa-solid fa-book-open"></i>Knowledge Hub
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('exam-predictor*') ? 'active' : '' }}">
                            <a href="{{ route('exam-predictor.index') }}" class="sidebar-link">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>Exam Predictor
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('focus*') ? 'active' : '' }}">
                            <a href="{{ route('focus.index') }}" class="sidebar-link">
                                <i class="fa-solid fa-clock"></i>Focus Mode
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('habits*') ? 'active' : '' }}">
                            <a href="{{ route('habits.index') }}" class="sidebar-link">
                                <i class="fa-solid fa-circle-check"></i>Habit Tracker
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('analytics*') ? 'active' : '' }}">
                            <a href="{{ route('analytics.index') }}" class="sidebar-link">
                                <i class="fa-solid fa-chart-pie"></i>Analytics
                            </a>
                        </li>
                        <li class="sidebar-item {{ request()->is('subscription*') ? 'active' : '' }}">
                            <a href="{{ route('subscription.index') }}" class="sidebar-link">
                                <i class="fa-solid fa-gem"></i>Upgrade Premium
                            </a>
                        </li>
                    @endif
                    
                    <li class="sidebar-item {{ request()->is('profile*') ? 'active' : '' }}">
                        <a href="{{ route('profile.edit') }}" class="sidebar-link">
                            <i class="fa-solid fa-user-gear"></i>Pengaturan Profil
                        </a>
                    </li>
                </ul>

                <!-- Permanent Sidebar Focus Timer Widget -->
                @if(auth()->user()->role !== 'admin')
                <div class="sidebar-focus-widget">
                    <div class="focus-widget-title">Focus Session</div>
                    <div class="focus-widget-timer text-center widget-timer-display">25:00</div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-grow-1 widget-start-btn" onclick="toggleWidgetTimer()">Mulai</button>
                        <button class="btn btn-outline-light btn-sm widget-reset-btn" onclick="resetWidgetTimer()"><i class="fa-solid fa-rotate-right"></i></button>
                    </div>
                </div>
                @endif

                <!-- Global Theme Toggle Widget -->
                <div class="px-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center p-2.5 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color);">
                        <span class="small text-secondary fw-semibold"><i class="fa-solid fa-circle-half-stroke me-2"></i>Tema</span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-light py-0.5 px-2 theme-toggle-btn" data-theme-val="light" onclick="setGlobalTheme('light')"><i class="fa-solid fa-sun"></i></button>
                            <button type="button" class="btn btn-outline-light py-0.5 px-2 theme-toggle-btn" data-theme-val="dark" onclick="setGlobalTheme('dark')"><i class="fa-solid fa-moon"></i></button>
                            <button type="button" class="btn btn-outline-light py-0.5 px-2 theme-toggle-btn" data-theme-val="system" onclick="setGlobalTheme('system')"><i class="fa-solid fa-desktop"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logged-in User Profile Card -->
            <div class="user-card-sm">
                <img src="{{ auth()->user()->foto_profil_url }}" alt="Foto Profil" class="user-avatar-sm">
                <div class="overflow-hidden flex-grow-1">
                    <div class="user-name-sm text-truncate">{{ auth()->user()->name }}</div>
                    <div class="user-role-sm text-capitalize">{{ auth()->user()->role === 'admin' ? 'Administrator' : 'Mahasiswa' }}</div>
                </div>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-danger ms-2" title="Keluar">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <main class="main-content">
            <!-- Toast Notifications (Handled by SweetAlert2) -->
            @if(session('success') || session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.onmouseenter = Swal.stopTimer;
                                toast.onmouseleave = Swal.resumeTimer;
                            }
                        });
                        @if(session('success'))
                            Toast.fire({
                                icon: 'success',
                                title: {!! json_encode(session('success')) !!}
                            });
                        @endif
                        @if(session('error'))
                            Toast.fire({
                                icon: 'error',
                                title: {!! json_encode(session('error')) !!}
                            });
                        @endif
                    });
                </script>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap 5 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Permanent Pomodoro Sidebar Widget JavaScript Logic -->
    <script>
        let widgetTimeRemaining = 25 * 60; // 25 Menit
        let widgetTimerInterval = null;
        let widgetIsRunning = false;
        
        function updateWidgetDisplay() {
            let minutes = Math.floor(widgetTimeRemaining / 60);
            let seconds = widgetTimeRemaining % 60;
            const timeStr = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
            document.querySelectorAll('.widget-timer-display').forEach(el => el.innerText = timeStr);
        }

        function toggleWidgetTimer() {
            const btns = document.querySelectorAll('.widget-start-btn');
            if (widgetIsRunning) {
                // Pause
                clearInterval(widgetTimerInterval);
                widgetIsRunning = false;
                btns.forEach(btn => {
                    btn.innerText = 'Lanjutkan';
                    btn.classList.replace('btn-secondary', 'btn-primary');
                });
            } else {
                // Start
                widgetIsRunning = true;
                btns.forEach(btn => {
                    btn.innerText = 'Jeda';
                    btn.classList.replace('btn-primary', 'btn-secondary');
                });
                
                widgetTimerInterval = setInterval(() => {
                    if (widgetTimeRemaining > 0) {
                        widgetTimeRemaining--;
                        updateWidgetDisplay();
                    } else {
                        // Timer Selesai
                        clearInterval(widgetTimerInterval);
                        widgetIsRunning = false;
                        
                        // Kirim request ke backend untuk catat sesi fokus dan tambah XP/Streak
                        fetch('/focus/complete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSR-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ duration: 1500, completed: true })
                        }).then(() => {
                            Swal.fire({
                                title: 'Sesi Selesai!',
                                text: 'Waktu fokus selesai! Sesi belajar berhasil tercatat (+100 XP).',
                                icon: 'success',
                                confirmButtonText: 'Lanjutkan',
                                confirmButtonColor: '#6366f1'
                            }).then(() => {
                                window.location.reload();
                            });
                        });
                        
                        resetWidgetTimer();
                    }
                }, 1000);
            }
        }

        function resetWidgetTimer() {
            clearInterval(widgetTimerInterval);
            widgetIsRunning = false;
            widgetTimeRemaining = 25 * 60;
            updateWidgetDisplay();
            document.querySelectorAll('.widget-start-btn').forEach(btn => {
                btn.innerText = 'Mulai';
                btn.classList.remove('btn-secondary');
                btn.classList.add('btn-primary');
            });
        }

        // Global Theme Toggle Logic
        function setGlobalTheme(theme) {
            localStorage.setItem('theme_preference', theme);
            
            let themeToApply = theme;
            if (theme === 'system') {
                themeToApply = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', themeToApply);

            // Update visual active state of theme toggle buttons
            updateThemeToggleButtons(theme);

            // Sync with profile edit dropdown if it exists on page
            const profileThemeSelect = document.getElementById('theme_preference');
            if (profileThemeSelect) {
                profileThemeSelect.value = theme;
            }

            // Save to database via Fetch API
            fetch("{{ route('profile.theme') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSR-Token": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ theme_preference: theme })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: false
                    });
                    Toast.fire({
                        icon: 'success',
                        title: data.message
                    });
                }
            })
            .catch(err => console.error("Gagal mengupdate tema di database", err));
        }

        function updateThemeToggleButtons(activeTheme) {
            document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
                if (btn.getAttribute('data-theme-val') === activeTheme) {
                    btn.classList.remove('btn-outline-light');
                    btn.classList.add('btn-primary');
                    btn.style.color = '#ffffff';
                } else {
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-outline-light');
                    btn.style.color = '';
                }
            });
        }

        // Set active buttons state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const currentPref = localStorage.getItem('theme_preference') || 'system';
            updateThemeToggleButtons(currentPref);
        });
    </script>
    @yield('scripts')
</body>
</html>
