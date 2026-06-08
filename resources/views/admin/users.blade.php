@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-5 justify-content-between align-items-center">
        <div class="col-md-8">
            <h1 class="fw-bold">User Management</h1>
            <p class="text-secondary">Lihat daftar pengguna, ubah hak akses (role), nonaktifkan akun (suspend), atau hapus akun pengguna secara permanen.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <!-- Search bar -->
            <form action="{{ route('admin.users') }}" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm bg-dark border-secondary text-white" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama atau email...">
                    <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- User Table -->
    <div class="glass-card p-4">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr class="text-secondary" style="border-bottom: 1px solid rgba(255,255,255,0.08);">
                        <th class="py-3">Pengguna</th>
                        <th class="py-3">Jurusan / Semester</th>
                        <th class="py-3">Role</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Tanggal Daftar</th>
                        <th class="py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td class="py-3 d-flex align-items-center">
                                <img src="{{ $u->foto_profil ? asset($u->foto_profil) : 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($u->name) }}" alt="Avatar" class="rounded-circle me-3" style="width: 40px; height: 40px; border:2px solid var(--color-primary); object-fit:cover;">
                                <div>
                                    <h6 class="mb-0 text-white">{{ $u->name }}</h6>
                                    <span class="text-secondary small">{{ $u->email }}</span>
                                </div>
                            </td>
                            <td class="py-3 text-secondary">{{ $u->jurusan ?? '-' }} / S{{ $u->semester ?? '-' }}</td>
                            <td class="py-3">
                                <span class="badge {{ $u->role === 'admin' ? 'bg-danger-subtle text-danger' : 'bg-indigo-subtle text-indigo' }} text-capitalize">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="py-3">
                                @if($u->is_suspended)
                                    <span class="badge bg-danger-subtle text-danger">Suspended</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">Aktif</span>
                                @endif
                            </td>
                            <td class="py-3 text-secondary">{{ $u->created_at->translatedFormat('d M Y') }}</td>
                            <td class="py-3 text-end">
                                @if($u->id === auth()->id())
                                    <button class="btn btn-sm btn-outline-light" disabled>Akun Anda</button>
                                @else
                                    <div class="d-inline-flex gap-2">
                                        <!-- Toggle Role Form -->
                                        <form action="{{ route('admin.users.toggle-role', $u->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-light">Ubah Role</button>
                                        </form>

                                        <!-- Suspend Form -->
                                        <form action="{{ route('admin.users.suspend', $u->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $u->is_suspended ? 'btn-success text-white' : 'btn-outline-light text-warning' }}">
                                                {{ $u->is_suspended ? 'Unsuspend' : 'Suspend' }}
                                            </button>
                                        </form>

                                         <!-- Delete Form -->
                                         <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST">
                                             @csrf
                                             @method('DELETE')
                                             <button type="button" class="btn btn-sm btn-outline-light text-danger" onclick="confirmDeleteUser(this)">Hapus</button>
                                         </form>
                                     </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDeleteUser(button) {
    const form = button.closest('form');
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Hapus user secara permanen? Tindakan ini tidak dapat dibatalkan.",
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
