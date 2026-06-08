@extends('layouts.app')

@section('title', 'Smart Tasks')

@section('content')
<div class="container-fluid">
    <div class="row mb-5 justify-content-between align-items-center">
        <div class="col-md-8">
            <h1 class="fw-bold">Smart Task Management</h1>
            <p class="text-secondary">Kelola tugas kuliah Anda dengan prioritas cerdas berbasis AI.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i class="fa-solid fa-plus me-2"></i> Tambah Tugas
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="row g-3 mb-4 align-items-center">
        <div class="col-md-8 d-flex gap-2">
            <a href="{{ route('tasks.index') }}" class="btn btn-sm {{ !request()->has('status') && !request()->has('prioritas') ? 'btn-primary' : 'btn-outline-light' }}">Semua</a>
            <a href="{{ route('tasks.index', ['status' => 'todo']) }}" class="btn btn-sm {{ request()->status === 'todo' ? 'btn-primary' : 'btn-outline-light' }}">Todo</a>
            <a href="{{ route('tasks.index', ['status' => 'in_progress']) }}" class="btn btn-sm {{ request()->status === 'in_progress' ? 'btn-primary' : 'btn-outline-light' }}">Sedang Dikerjakan</a>
            <a href="{{ route('tasks.index', ['status' => 'completed']) }}" class="btn btn-sm {{ request()->status === 'completed' ? 'btn-primary' : 'btn-outline-light' }}">Selesai</a>
        </div>
        <div class="col-md-4 d-flex justify-content-md-end gap-2">
            <select class="form-select form-select-sm bg-dark border-secondary text-heading" style="width: 150px;" onchange="location = this.value;">
                <option value="{{ route('tasks.index', request()->except('prioritas')) }}">Semua Prioritas</option>
                <option value="{{ route('tasks.index', array_merge(request()->query(), ['prioritas' => 'high'])) }}" {{ request()->prioritas === 'high' ? 'selected' : '' }}>Tinggi</option>
                <option value="{{ route('tasks.index', array_merge(request()->query(), ['prioritas' => 'medium'])) }}" {{ request()->prioritas === 'medium' ? 'selected' : '' }}>Sedang</option>
                <option value="{{ route('tasks.index', array_merge(request()->query(), ['prioritas' => 'low'])) }}" {{ request()->prioritas === 'low' ? 'selected' : '' }}>Rendah</option>
            </select>
        </div>
    </div>

    <!-- Tasks Content -->
    @if($tasks->isEmpty())
        <div class="glass-card p-5">
            <div class="text-center py-5 text-secondary">
                <i class="fa-solid fa-list-check mb-3 fs-1 text-muted"></i>
                <h5>Belum ada tugas terdaftar</h5>
                <p class="mb-4">Tugas yang Anda buat akan muncul di sini. Anda bisa meminta AI membantu menyusun tingkat prioritas.</p>
                <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#addTaskModal">Buat Tugas Pertama</button>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($tasks as $task)
                <div class="col-md-6 col-lg-4" id="task-card-{{ $task->id }}">
                    <div class="glass-card p-4 h-100 d-flex flex-column position-relative overflow-hidden">
                        <!-- Top Bar info -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge {{ $task->prioritas === 'high' ? 'bg-danger-subtle text-danger' : ($task->prioritas === 'medium' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success') }} text-capitalize">
                                Prioritas: {{ $task->prioritas }}
                            </span>
                            <span class="text-secondary small">
                                <i class="fa-solid fa-clock me-1"></i> {{ $task->deadline ? $task->deadline->diffForHumans() : 'No deadline' }}
                            </span>
                        </div>

                        <!-- Checkbox + Title -->
                        <div class="d-flex align-items-start gap-3 mb-2">
                            <input type="checkbox" class="form-check-input mt-1" style="width:20px; height:20px; cursor:pointer;" 
                                   {{ $task->status === 'completed' ? 'checked' : '' }} 
                                   onclick="toggleTaskStatus({{ $task->id }})">
                            <h5 class="mb-0 {{ $task->status ==='completed' ? 'text-decoration-line-through text-secondary' : '' }}" id="task-title-{{ $task->id }}">
                                {{ $task->judul }}
                            </h5>
                        </div>

                        <!-- Description -->
                        <p class="text-secondary small flex-grow-1 ps-4 ms-2 mb-4">
                            {{ $task->deskripsi ?? 'Tidak ada deskripsi.' }}
                        </p>

                        <!-- Footer Actions -->
                        <div class="d-flex justify-content-between align-items-center border-top border-secondary-subtle pt-3 ps-4 ms-2 mt-auto">
                            <!-- AI Priority Assistant -->
                            <button class="btn btn-sm btn-outline-light text-indigo px-3" onclick="runAiPriority({{ $task->id }})" title="Minta AI tentukan prioritas">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> AI Prioritize
                            </button>
                            <div class="d-flex gap-2">
                                <!-- Edit Button -->
                                <button class="btn btn-sm btn-outline-light text-white" onclick="openEditModal({{ json_encode($task) }})">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <!-- Delete Form -->
                                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-light text-danger" onclick="confirmDeleteTask(this)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Modal Tambah Tugas -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-heading" style="border-radius:16px;">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Tambah Tugas Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTaskForm" action="{{ route('tasks.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label">Judul Tugas</label>
                        <input type="text" class="form-control bg-dark border-secondary text-heading" id="taskTitle" name="judul" required placeholder="Contoh: Laporan Algoritma Struktur Data">
                    </div>
                    <div class="mb-3">
                        <label for="taskDesc" class="form-label">Deskripsi & Instruksi</label>
                        <textarea class="form-control bg-dark border-secondary text-heading" id="taskDesc" name="deskripsi" rows="3" placeholder="Masukkan instruksi pengerjaan..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="taskDeadline" class="form-label">Tenggat Waktu</label>
                            <input type="datetime-local" class="form-control bg-dark border-secondary text-heading" id="taskDeadline" name="deadline">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="taskPriority" class="form-label">Prioritas</label>
                            <select class="form-select bg-dark border-secondary text-heading" id="taskPriority" name="prioritas">
                                <option value="low">Rendah</option>
                                <option value="medium" selected>Sedang</option>
                                <option value="high">Tinggi</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary px-0 pb-0 mt-4">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Tugas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Tugas -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-heading" style="border-radius:16px;">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Edit Tugas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTaskForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="editTaskTitle" class="form-label">Judul Tugas</label>
                        <input type="text" class="form-control bg-dark border-secondary text-heading" id="editTaskTitle" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTaskDesc" class="form-label">Deskripsi & Instruksi</label>
                        <textarea class="form-control bg-dark border-secondary text-heading" id="editTaskDesc" name="deskripsi" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="editTaskDeadline" class="form-label">Tenggat Waktu</label>
                            <input type="datetime-local" class="form-control bg-dark border-secondary text-heading" id="editTaskDeadline" name="deadline">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="editTaskPriority" class="form-label">Prioritas</label>
                            <select class="form-select bg-dark border-secondary text-heading" id="editTaskPriority" name="prioritas">
                                <option value="low">Rendah</option>
                                <option value="medium">Sedang</option>
                                <option value="high">Tinggi</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editTaskStatus" class="form-label">Status</label>
                        <select class="form-select bg-dark border-secondary text-heading" id="editTaskStatus" name="status">
                            <option value="todo">Todo</option>
                            <option value="in_progress">Sedang Dikerjakan</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                    <div class="modal-footer border-secondary px-0 pb-0 mt-4">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Toggle Status Task via AJAX
    function toggleTaskStatus(taskId) {
        fetch(`/tasks/${taskId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSR-Token': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update title style
                const title = document.getElementById(`task-title-${taskId}`);
                if (data.status === 'completed') {
                    title.classList.add('text-decoration-line-through', 'text-secondary');
                } else {
                    title.classList.remove('text-decoration-line-through', 'text-secondary');
                }
                
                // Show notification toast
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: data.message
                }).then(() => {
                    window.location.reload();
                });
            }
        });
    }

    // AI Prioritize via AJAX
    function runAiPriority(taskId) {
        // Show loading indicator
        Swal.fire({
            title: 'Asisten Prioritas AI',
            text: 'AI sedang menganalisis dokumen tugas dan tenggat waktu Anda...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch(`/tasks/${taskId}/ai-prioritize`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSR-Token': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                Swal.fire({
                    title: 'Analisis Selesai!',
                    html: `Prioritas baru ditentukan: <span class="badge bg-indigo-subtle text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill fs-6">${data.prioritas.toUpperCase()}</span><br><br><strong>Penjelasan AI:</strong><br>${data.explanation}`,
                    icon: 'success',
                    confirmButtonText: 'Terapkan',
                    confirmButtonColor: '#6366f1'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Gagal menjalankan asisten prioritas AI.',
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            }
        })
        .catch(err => {
            Swal.close();
            Swal.fire({
                title: 'Error!',
                text: 'Koneksi terputus saat menghubungi AI.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        });
    }

    // Confirm Delete Task
    function confirmDeleteTask(button) {
        const form = button.closest('form');
        Swal.fire({
            title: 'Hapus Tugas?',
            text: "Tugas ini akan dihapus secara permanen.",
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

    // Open Edit Modal and fill form values
    function openEditModal(task) {
        const form = document.getElementById('editTaskForm');
        form.action = `/tasks/${task.id}`;
        
        document.getElementById('editTaskTitle').value = task.judul;
        document.getElementById('editTaskDesc').value = task.deskripsi || '';
        
        if (task.deadline) {
            // Format to YYYY-MM-DDTHH:MM
            const date = new Date(task.deadline);
            const formatted = date.toISOString().slice(0, 16);
            document.getElementById('editTaskDeadline').value = formatted;
        } else {
            document.getElementById('editTaskDeadline').value = '';
        }
        
        document.getElementById('editTaskPriority').value = task.prioritas;
        document.getElementById('editTaskStatus').value = task.status;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editTaskModal'));
        modal.show();
    }
</script>
@endsection
