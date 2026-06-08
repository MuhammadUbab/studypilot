<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - StudyPilot</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Custom Dark Mode CSS -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>
<body>

    <div class="auth-container">
        <div class="glass-card auth-card shadow-lg">
            <div class="text-center mb-4">
                <a href="{{ route('landing') }}" class="text-decoration-none fs-2 fw-bold text-heading">
                    <i class="fa-solid fa-paper-plane me-2" style="color: var(--color-primary);"></i>StudyPilot
                </a>
                <p class="text-secondary mt-2">Masuk ke akun belajar Anda</p>
            </div>

            <!-- Error Messages -->
            @if($errors->any())
                <div class="alert alert-danger border-0 bg-danger-subtle text-danger py-2 rounded-3 mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success border-0 bg-success-subtle text-success py-2 rounded-3 mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="password" class="form-label">Password</label>
                        <a href="{{ route('password.request') }}" class="text-indigo text-decoration-none" style="font-size:0.8rem; color: var(--color-primary);">Lupa Password?</a>
                    </div>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label text-secondary" style="font-size:0.875rem;" for="remember">Ingat Saya</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2.5 mb-3">Masuk</button>

                <div class="text-center">
                    <p class="text-secondary mb-0" style="font-size:0.875rem;">
                        Belum punya akun? <a href="{{ route('register') }}" class="text-indigo text-decoration-none fw-semibold" style="color: var(--color-primary);">Daftar Gratis</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
