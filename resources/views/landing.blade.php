<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyPilot - Belajar Lebih Pintar, Tugas Lebih Teratur, Nilai Lebih Maksimal</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Custom Dark Mode CSS -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>
<body style="background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.12) 0%, transparent 50%), var(--bg-main);">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-transparent py-4">
        <div class="container">
            <a class="navbar-brand fs-3 fw-bold" href="#">
                <i class="fa-solid fa-paper-plane me-2 text-indigo" style="color: var(--color-primary);"></i>StudyPilot
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link text-light px-3" href="#fitur">Fitur</a></li>
                    <li class="nav-item"><a class="nav-link text-light px-3" href="#cara-kerja">Cara Kerja</a></li>
                    <li class="nav-item"><a class="nav-link text-light px-3" href="#pricing">Harga</a></li>
                    <li class="nav-item"><a class="nav-link text-light px-3" href="#faq">FAQ</a></li>
                </ul>
                <div class="d-flex gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">Masuk ke Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-light">Masuk</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">Coba Sekarang</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="container py-5 my-5 text-center position-relative">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <span class="badge bg-indigo-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill mb-4" style="background-color: var(--color-primary-glow);">
                    ✨ Academic Operating System Berbasis AI
                </span>
                <h1 class="display-3 fw-bold tracking-tight mb-4" style="background: linear-gradient(135deg, #ffffff 30%, var(--text-secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Belajar Lebih Pintar, Tugas Lebih Teratur, Nilai Lebih Maksimal.
                </h1>
                <p class="fs-5 text-secondary mb-5 px-md-5">
                    StudyPilot adalah platform all-in-one yang menggabungkan manajemen tugas pintar, ringkasan materi AI, pembuatan kuis otomatis, prediksi ujian, focus session Pomodoro, dan analitik akademik dalam satu portal modern.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4 py-3">Mulai Gratis Sekarang</a>
                    <a href="#fitur" class="btn btn-outline-light btn-lg px-4 py-3">Pelajari Selengkapnya</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Fitur Unggulan -->
    <section id="fitur" class="container py-5 my-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Fitur Unggulan StudyPilot</h2>
            <p class="text-secondary">Ekosistem pembelajaran berbasis AI yang mengoptimalkan seluruh kegiatan akademik Anda.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="glass-card stat-card h-100">
                    <div class="stat-icon primary"><i class="fa-solid fa-list-check"></i></div>
                    <h4>Smart Task Management</h4>
                    <p class="text-secondary">Mengelola tugas kuliah dengan filter prioritas bertenaga AI untuk merekomendasikan tenggat waktu terbaik.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card stat-card h-100">
                    <div class="stat-icon success"><i class="fa-solid fa-brain"></i></div>
                    <h4>AI Study Planner</h4>
                    <p class="text-secondary">Buat rencana jadwal belajar harian otomatis berdasarkan waktu luang dan prioritas tugas Anda secara instan.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card stat-card h-100">
                    <div class="stat-icon warning"><i class="fa-solid fa-book-open"></i></div>
                    <h4>Knowledge Hub & AI Notes</h4>
                    <p class="text-secondary">Upload PDF, PPTX, Video, dan YouTube untuk diringkas AI menjadi poin penting, glosarium, serta Mind Map.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card stat-card h-100">
                    <div class="stat-icon danger"><i class="fa-solid fa-circle-question"></i></div>
                    <h4>AI Quiz Generator</h4>
                    <p class="text-secondary">Hasilkan kuis pilihan ganda, essay, true/false, dan HOTS otomatis untuk menguji pemahaman materi kuliah.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card stat-card h-100">
                    <div class="stat-icon primary"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                    <h4>AI Exam Predictor</h4>
                    <p class="text-secondary">Unggah soal ujian lama untuk menganalisis pola soal, memprediksi topik yang akan keluar, dan mengukur kesiapan ujian.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card stat-card h-100">
                    <div class="stat-icon success"><i class="fa-solid fa-hourglass-half"></i></div>
                    <h4>Pomodoro Focus System</h4>
                    <p class="text-secondary">Belajar lebih fokus dengan widget timer Pomodoro terintegrasi. Kumpulkan XP dan pertahankan streak belajar Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cara Kerja -->
    <section id="cara-kerja" class="container py-5 my-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Cara Kerja</h2>
            <p class="text-secondary">Hanya butuh 3 langkah mudah untuk mulai belajar dengan lebih pintar.</p>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4">
                    <div class="d-inline-flex bg-indigo-subtle border border-primary-subtle text-primary fw-bold fs-4 rounded-circle align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background-color: var(--color-primary-glow);">1</div>
                    <h4>Daftar & Upload Materi</h4>
                    <p class="text-secondary">Buat akun gratis Anda, lalu unggah materi kuliah atau catat tugas kuliah Anda di sistem.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <div class="d-inline-flex bg-indigo-subtle border border-primary-subtle text-primary fw-bold fs-4 rounded-circle align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background-color: var(--color-primary-glow);">2</div>
                    <h4>Biarkan AI Bekerja</h4>
                    <p class="text-secondary">AI akan menganalisis dokumen materi untuk merangkum, membuat jadwal belajar, atau menyusun kuis interaktif.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <div class="d-inline-flex bg-indigo-subtle border border-primary-subtle text-primary fw-bold fs-4 rounded-circle align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background-color: var(--color-primary-glow);">3</div>
                    <h4>Fokus Belajar & Evaluasi</h4>
                    <p class="text-secondary">Gunakan Focus Timer saat belajar, kerjakan kuis evaluasi otomatis, dan raih nilai akademik maksimal.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="container py-5 my-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Pilih Paket Belajar Anda</h2>
            <p class="text-secondary">Dapatkan paket pembelajaran terbaik yang sesuai dengan anggaran dan kebutuhan akademik Anda.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <!-- Free Plan -->
            <div class="col-lg-4">
                <div class="glass-card stat-card h-100 d-flex flex-column">
                    <h5 class="text-secondary text-uppercase tracking-wider">Free</h5>
                    <div class="my-3">
                        <span class="display-5 fw-bold text-heading">Rp0</span>
                    </div>
                    <p class="text-secondary mb-4">Solusi dasar untuk mencoba sistem manajemen akademik.</p>
                    <ul class="list-unstyled mb-5 flex-grow-1">
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Maksimal 10 tugas aktif</li>
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> 3 ringkasan AI per hari</li>
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> 3 kuis AI per hari</li>
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> 1 AI study planner per hari</li>
                        <li class="mb-3 text-muted"><i class="fa-solid fa-circle-xmark text-danger me-2"></i> Chat materi kuliah tanpa batas</li>
                        <li class="mb-3 text-muted"><i class="fa-solid fa-circle-xmark text-danger me-2"></i> AI Exam Predictor</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline-light w-100">Mulai Gratis</a>
                </div>
            </div>
            <!-- Premium Student -->
            <div class="col-lg-4">
                <div class="glass-card stat-card h-100 d-flex flex-column border-primary shadow-lg" style="border: 1px solid var(--color-primary) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="text-indigo text-uppercase tracking-wider" style="color: var(--color-primary);">Premium Student</h5>
                        <span class="badge bg-primary rounded-pill px-3 py-1">Paling Populer</span>
                    </div>
                    <div class="my-3">
                        <span class="display-5 fw-bold text-heading">Rp24.900</span>
                        <span class="text-secondary">/bulan</span>
                    </div>
                    <p class="text-secondary mb-4">Semua fitur esensial yang dibutuhkan mahasiswa produktif.</p>
                    <ul class="list-unstyled mb-5 flex-grow-1">
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Tugas aktif tanpa batas</li>
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Ringkasan materi tanpa batas</li>
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Kuis AI tanpa batas</li>
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Chat materi tanpa batas</li>
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Study planner harian tanpa batas</li>
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Academic Analytics lengkap</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-primary w-100">Coba Sekarang</a>
                </div>
            </div>
            <!-- Premium Plus -->
            <div class="col-lg-4">
                <div class="glass-card stat-card h-100 d-flex flex-column">
                    <h5 class="text-secondary text-uppercase tracking-wider">Premium Plus</h5>
                    <div class="my-3">
                        <span class="display-5 fw-bold text-heading">Rp39.900</span>
                        <span class="text-secondary">/bulan</span>
                    </div>
                    <p class="text-secondary mb-4">Maksimalkan performa ujian Anda dengan fitur prediksi canggih.</p>
                    <ul class="list-unstyled mb-5 flex-grow-1">
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Semua fitur Premium Student</li>
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> AI Exam Predictor</li>
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Exam Readiness Score</li>
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Prioritas antrean proses AI</li>
                        <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Insight akademik mendalam</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-outline-light w-100">Beli Premium Plus</a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="container py-5 my-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Pertanyaan yang Sering Diajukan</h2>
            <p class="text-secondary">Temukan jawaban untuk pertanyaan umum mengenai platform StudyPilot.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion accordion-flush" id="faqAccordion">
                    <div class="accordion-item bg-transparent border-bottom border-secondary-subtle">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent text-heading py-4 fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Apakah kuis otomatis yang dihasilkan AI relevan dengan materi saya?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary">
                                Ya, AI di StudyPilot menganalisis dokumen materi kuliah yang Anda unggah secara mendalam dan merancang pertanyaan kuis khusus berdasarkan isi dokumen tersebut (PDF, PPTX, DOCX, maupun tautan video).
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item bg-transparent border-bottom border-secondary-subtle">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent text-heading py-4 fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Bagaimana cara AI memprediksi materi ujian?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary">
                                AI Exam Predictor menganalisis silabus, kisi-kisi kuliah, atau soal ujian (UTS/UAS) tahun-tahun sebelumnya yang Anda unggah, mendeteksi pola materi berulang, lalu memprediksi topik penting apa yang kemungkinan besar akan diujikan lagi.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item bg-transparent border-bottom border-secondary-subtle">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent text-heading py-4 fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Apakah platform ini berbayar?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-secondary">
                                Kami menyediakan paket Free dengan kuota dasar yang dapat digunakan gratis selamanya. Namun, Anda juga dapat meningkatkan ke Premium Student atau Premium Plus untuk menikmati fitur tanpa batas.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-top border-secondary-subtle py-5 mt-5 bg-dark-subtle" style="background-color: var(--bg-sidebar) !important;">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <span class="fs-4 fw-bold text-heading">
                        <i class="fa-solid fa-paper-plane me-2 text-primary"></i>StudyPilot
                    </span>
                    <p class="text-secondary mt-2 mb-0">© 2026 StudyPilot. Seluruh Hak Cipta Dilindungi.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-secondary text-decoration-none mx-2 hover-white">Ketentuan Layanan</a>
                    <a href="#" class="text-secondary text-decoration-none mx-2 hover-white">Kebijakan Privasi</a>
                    <a href="#" class="text-secondary text-decoration-none mx-2 hover-white">Hubungi Kami</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
