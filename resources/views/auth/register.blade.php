<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - StudyPilot</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Custom Dark Mode CSS -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>
<body>

    <div class="auth-container py-5">
        <div class="glass-card auth-card shadow-lg" style="max-width:550px;">
            <div class="text-center mb-4">
                <a href="{{ route('landing') }}" class="text-decoration-none fs-2 fw-bold text-heading">
                    <i class="fa-solid fa-paper-plane me-2" style="color: var(--color-primary);"></i>StudyPilot
                </a>
                <p class="text-secondary mt-2">Buat akun untuk memulai belajar pintar</p>
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

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Nama Anda" required autofocus>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="education_level" class="form-label">Tingkatan Akademik</label>
                    <select class="form-select form-control" id="education_level" name="education_level" required onchange="toggleAcademicFields(this.value)">
                        <option value="pelajar" {{ old('education_level') == 'pelajar' ? 'selected' : '' }}>Pelajar</option>
                        <option value="mahasiswa" {{ old('education_level', 'mahasiswa') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="guru_dosen" {{ old('education_level') == 'guru_dosen' ? 'selected' : '' }}>Guru/Dosen (Pengajar)</option>
                    </select>
                </div>

                <div class="row" id="academic-details-row">
                    <div class="col-md-6 mb-3">
                        <label for="jurusan" class="form-label" id="label-jurusan">Jurusan Kuliah / Bidang Studi</label>
                        <input type="text" class="form-control" id="jurusan" name="jurusan" value="{{ old('jurusan') }}" placeholder="e.g. Teknik Informatika">
                    </div>
                    
                    <div class="col-md-6 mb-3" id="semester-container">
                        <label for="semester" class="form-label">Semester Saat Ini</label>
                        <input type="number" class="form-control" id="semester" name="semester" value="{{ old('semester') }}" min="1" max="14" placeholder="e.g. 4">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ketik ulang password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2.5 mb-3">Daftar Sekarang</button>

                <div class="text-center">
                    <p class="text-secondary mb-0" style="font-size:0.875rem;">
                        Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo text-decoration-none fw-semibold" style="color: var(--color-primary);">Masuk</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleAcademicFields(value) {
            const labelJurusan = document.getElementById('label-jurusan');
            const inputJurusan = document.getElementById('jurusan');
            const semesterContainer = document.getElementById('semester-container');
            const inputSemester = document.getElementById('semester');

            if (value === 'pelajar') {
                labelJurusan.innerText = 'Nama Sekolah / Kelas';
                inputJurusan.placeholder = 'e.g. SMA Negeri 1 Jakarta (Kelas XII)';
                semesterContainer.style.display = 'none';
                inputSemester.removeAttribute('required');
                inputSemester.value = '';
            } else if (value === 'guru_dosen') {
                labelJurusan.innerText = 'Nama Sekolah/Kampus & Mata Pelajaran';
                inputJurusan.placeholder = 'e.g. Universitas Indonesia (Kalkulus)';
                semesterContainer.style.display = 'none';
                inputSemester.removeAttribute('required');
                inputSemester.value = '';
            } else {
                labelJurusan.innerText = 'Jurusan Kuliah / Bidang Studi';
                inputJurusan.placeholder = 'e.g. Teknik Informatika';
                semesterContainer.style.display = 'block';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleAcademicFields(document.getElementById('education_level').value);
        });
    </script>
</body>
</html>
