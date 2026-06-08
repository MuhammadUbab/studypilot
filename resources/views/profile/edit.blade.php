@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="container-fluid" style="max-width: 800px; margin: 0 auto;">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold">Pengaturan Profil</h1>
            <p class="text-secondary">Kelola informasi pribadi dan detail akademik Anda.</p>
        </div>
    </div>

    <div class="glass-card p-4">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Foto Profil Section -->
            <div class="d-flex align-items-center mb-5 gap-4">
                <div class="position-relative">
                    <img src="{{ $user->foto_profil_url }}" 
                         alt="Avatar" class="rounded-circle border border-primary border-3" 
                         style="width: 100px; height: 100px; object-fit: cover;" id="avatar-preview">
                </div>
                <div>
                    <h5 class="text-white mb-2">Foto Profil</h5>
                    <p class="text-secondary small mb-3">Maksimum ukuran file: 2MB (JPG, JPEG, PNG, GIF)</p>
                    <input type="file" name="foto_profil" id="foto_profil" class="form-control form-control-sm" 
                           onchange="previewImage(event)" style="background: rgba(255,255,255,0.05); color:#fff;">
                </div>
            </div>

            <!-- Form Fields -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="education_level" class="form-label">Tingkatan Akademik</label>
                    <select class="form-select form-control" id="education_level" name="education_level" required onchange="toggleAcademicFields(this.value)">
                        <option value="pelajar" {{ old('education_level', $user->education_level) == 'pelajar' ? 'selected' : '' }}>Pelajar</option>
                        <option value="mahasiswa" {{ old('education_level', $user->education_level) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="guru_dosen" {{ old('education_level', $user->education_level) == 'guru_dosen' ? 'selected' : '' }}>Guru/Dosen (Pengajar)</option>
                    </select>
                </div>
            </div>

            <div class="row mb-4" id="academic-details-row">
                <div class="col-md-6 mb-3">
                    <label for="jurusan" class="form-label" id="label-jurusan">Jurusan Kuliah / Bidang Studi</label>
                    <input type="text" class="form-control" id="jurusan" name="jurusan" value="{{ old('jurusan', $user->jurusan) }}" placeholder="e.g. Teknik Informatika">
                </div>
                <div class="col-md-6 mb-3" id="semester-container">
                    <label for="semester" class="form-label">Semester</label>
                    <input type="number" class="form-control" id="semester" name="semester" value="{{ old('semester', $user->semester) }}" min="1" max="14" placeholder="e.g. 4">
                </div>
            </div>

            <hr class="my-4" style="border-color: var(--border-color);">

            <h4 class="fw-bold mb-3 text-white">Tampilan</h4>
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="theme_preference" class="form-label">Tema Aplikasi</label>
                    <select class="form-select form-control" id="theme_preference" name="theme_preference" required>
                        <option value="light" {{ old('theme_preference', $user->theme_preference) == 'light' ? 'selected' : '' }}>Light Mode (Terang)</option>
                        <option value="dark" {{ old('theme_preference', $user->theme_preference) == 'dark' ? 'selected' : '' }}>Dark Mode (Gelap)</option>
                        <option value="system" {{ old('theme_preference', $user->theme_preference) == 'system' ? 'selected' : '' }}>System Mode (Mengikuti OS)</option>
                    </select>
                    <small class="text-secondary mt-1 d-block">Pilih preferensi tema visual untuk seluruh halaman aplikasi.</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary px-4 py-2.5">
                <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Perubahan
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('avatar-preview');
            output.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }

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

        // Real-time theme preview
        document.getElementById('theme_preference').addEventListener('change', function() {
            let selectedTheme = this.value;
            localStorage.setItem('theme_preference', selectedTheme);
            
            let themeToApply = selectedTheme;
            if (selectedTheme === 'system') {
                themeToApply = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            
            document.documentElement.setAttribute('data-theme', themeToApply);
        });
    });
</script>
@endsection
