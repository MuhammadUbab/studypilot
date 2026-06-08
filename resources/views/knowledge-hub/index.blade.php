@extends('layouts.app')

@section('title', 'Knowledge Hub')

@section('content')
<div class="container-fluid">
    <div class="row mb-5 justify-content-between align-items-center">
        <div class="col-md-8">
            <h1 class="fw-bold">Knowledge Hub & AI Assistant</h1>
            <p class="text-secondary">Pusat materi belajar pribadi Anda. Unggah materi untuk menghasilkan ringkasan, Mind Map, dan kuis AI.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadMaterialModal">
                <i class="fa-solid fa-cloud-arrow-up me-2"></i> Unggah Materi
            </button>
        </div>
    </div>

    <!-- Materials Grid -->
    @if($materials->isEmpty())
        <div class="glass-card p-5">
            <div class="text-center py-5 text-secondary">
                <i class="fa-solid fa-book-open mb-3 fs-1 text-muted"></i>
                <h5>Workspace materi Anda kosong</h5>
                <p class="mb-4">Unggah file PDF, DOCX, PPTX, atau tautkan link video YouTube untuk menghasilkan asisten belajar instan.</p>
                <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#uploadMaterialModal">Unggah Dokumen Pertama</button>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($materials as $material)
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card p-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                @php
                                    $badgeClass = 'bg-secondary-subtle text-secondary';
                                    $iconClass = 'fa-file-lines';
                                    if ($material->tipe_file === 'pdf') {
                                        $badgeClass = 'bg-danger-subtle text-danger';
                                        $iconClass = 'fa-file-pdf';
                                    } elseif ($material->tipe_file === 'docx') {
                                        $badgeClass = 'bg-primary-subtle text-primary';
                                        $iconClass = 'fa-file-word';
                                    } elseif ($material->tipe_file === 'pptx') {
                                        $badgeClass = 'bg-warning-subtle text-warning';
                                        $iconClass = 'fa-file-powerpoint';
                                    } elseif ($material->tipe_file === 'youtube') {
                                        $badgeClass = 'bg-danger-subtle text-danger';
                                        $iconClass = 'fa-play';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }} text-uppercase">
                                    <i class="fa-solid {{ $iconClass }} me-1"></i> {{ $material->tipe_file }}
                                </span>
                                <span class="text-secondary small">{{ $material->created_at->diffForHumans() }}</span>
                            </div>
                            <h5 class="mb-2 text-truncate" title="{{ $material->judul }}">{{ $material->judul }}</h5>
                            <p class="text-secondary small mb-4" style="display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden; text-overflow:ellipsis;">
                                {{ strip_tags($material->summary) }}
                            </p>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center border-top border-secondary-subtle pt-3 mt-3">
                            <a href="{{ route('knowledge-hub.show', $material->id) }}" class="btn btn-sm btn-primary px-3">
                                <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Buka Workspace
                            </a>
                            <form action="{{ route('knowledge-hub.destroy', $material->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-light text-danger" title="Hapus Materi" onclick="confirmDeleteMaterial(this)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Modal Unggah Materi -->
<div class="modal fade" id="uploadMaterialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-heading" style="border-radius:16px;">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Unggah Materi Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('knowledge-hub.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="materialTitle" class="form-label">Nama / Judul Materi</label>
                        <input type="text" class="form-control bg-dark border-secondary text-heading" id="materialTitle" name="judul" required placeholder="Contoh: Bab 2 - Struktur Sistem Operasi">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipe Sumber</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe_file" id="typePdf" value="pdf" checked onchange="toggleInputs('pdf')">
                                <label class="form-check-label" for="typePdf">PDF</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe_file" id="typeDocx" value="docx" onchange="toggleInputs('docx')">
                                <label class="form-check-label" for="typeDocx">DOCX</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe_file" id="typePptx" value="pptx" onchange="toggleInputs('pptx')">
                                <label class="form-check-label" for="typePptx">PPTX</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe_file" id="typeYoutube" value="youtube" onchange="toggleInputs('youtube')">
                                <label class="form-check-label" for="typeYoutube">YouTube</label>
                            </div>
                        </div>
                    </div>

                    <!-- File Input Group -->
                    <div class="mb-3" id="fileInputGroup">
                        <label for="fileUpload" class="form-label" id="fileUploadLabel">Pilih File PDF</label>
                        <input type="file" class="form-control bg-dark border-secondary text-heading" id="fileUpload" name="file_upload" accept=".pdf">
                    </div>

                    <!-- YouTube Input Group -->
                    <div class="mb-3 d-none" id="youtubeInputGroup">
                        <label for="youtubeUrl" class="form-label">Tautan Video YouTube</label>
                        <input type="url" class="form-control bg-dark border-secondary text-heading" id="youtubeUrl" name="youtube_url" placeholder="https://www.youtube.com/watch?v=...">
                    </div>

                    <div class="modal-footer border-secondary px-0 pb-0 mt-4">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" onclick="showLoadingText(this)">Unggah & Ekstrak AI</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleInputs(type) {
        if (type === 'youtube') {
            document.getElementById('fileInputGroup').classList.add('d-none');
            document.getElementById('fileUpload').removeAttribute('required');
            document.getElementById('youtubeInputGroup').classList.remove('d-none');
            document.getElementById('youtubeUrl').setAttribute('required', 'required');
        } else {
            document.getElementById('fileInputGroup').classList.remove('d-none');
            document.getElementById('fileUpload').setAttribute('required', 'required');
            document.getElementById('youtubeInputGroup').classList.add('d-none');
            document.getElementById('youtubeUrl').removeAttribute('required');
            
            const label = document.getElementById('fileUploadLabel');
            const fileInput = document.getElementById('fileUpload');
            if (type === 'pdf') {
                label.innerText = 'Pilih File PDF';
                fileInput.setAttribute('accept', '.pdf');
            } else if (type === 'docx') {
                label.innerText = 'Pilih File DOCX';
                fileInput.setAttribute('accept', '.docx');
            } else if (type === 'pptx') {
                label.innerText = 'Pilih File PPTX';
                fileInput.setAttribute('accept', '.pptx');
            }
        }
    }
    
    // Set initial validation
    document.getElementById('fileUpload').setAttribute('required', 'required');

    function showLoadingText(btn) {
        const title = document.getElementById('materialTitle').value;
        const file = document.getElementById('fileUpload').files.length;
        const yt = document.getElementById('youtubeUrl').value;
        const typeYoutube = document.getElementById('typeYoutube').checked;

        if (title && ((!typeYoutube && file) || (typeYoutube && yt))) {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Mengekstrak Dokumen & Ringkasan AI...';
            btn.setAttribute('disabled', 'disabled');
            btn.form.submit();
        }
    }

    function confirmDeleteMaterial(button) {
        const form = button.closest('form');
        Swal.fire({
            title: 'Hapus Materi Kuliah?',
            text: "Seluruh ringkasan, kuis, dan riwayat chat RAG materi ini akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>
@endsection
