@extends('layouts.app')

@section('title', 'Subscription Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="fw-bold">Subscription Management</h1>
            <p class="text-secondary">Ubah harga dan fitur masing-masing paket langganan SaaS, serta kelola masa aktif keanggotaan pengguna.</p>
        </div>
    </div>

    <!-- Package Editor Form -->
    <div class="glass-card p-4 mb-4">
        <h5 class="fw-bold mb-4"><i class="fa-solid fa-edit me-2 text-indigo" style="color:var(--color-primary);"></i>Pengaturan Harga Paket (Mock Editor)</h5>
        <form>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Harga Premium Student (Rp / bulan)</label>
                    <input type="number" class="form-control" value="24900" placeholder="e.g. 24900">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Harga Premium Plus (Rp / bulan)</label>
                    <input type="number" class="form-control" value="39900" placeholder="e.g. 39900">
                </div>
            </div>
            <button type="button" class="btn btn-primary mt-3" onclick="Swal.fire({title: 'Berhasil!', text: 'Pengaturan harga berhasil disimpan! (Simulasi)', icon: 'success', confirmButtonColor: '#6366f1'})">Simpan Perubahan</button>
        </form>
    </div>

    <!-- Active Subscribers List -->
    <div class="glass-card p-4">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-users-viewfinder me-2 text-success"></i>Daftar Pengguna Premium</h5>
        @if($premiumUsersList->isEmpty())
            <div class="text-center py-4 text-secondary">
                <i class="fa-solid fa-users-slash mb-3 fs-2 text-muted"></i>
                <p class="mb-0">Belum ada pengguna berbayar saat ini.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle border border-secondary-subtle">
                    <thead>
                        <tr>
                            <th>Pengguna</th>
                            <th>Paket</th>
                            <th>Tanggal Mulai</th>
                            <th>Masa Berlaku</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($premiumUsersList as $sub)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $sub->user->foto_profil_url }}" alt="Avatar" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover; border: 1px solid var(--color-primary);">
                                        <div>
                                            <div class="fw-bold text-white">{{ $sub->user->name }}</div>
                                            <small class="text-secondary">{{ $sub->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($sub->plan === 'premium_plus')
                                        <span class="badge bg-purple-subtle text-violet border border-violet-subtle">Premium Plus</span>
                                    @else
                                        <span class="badge bg-indigo-subtle text-primary border border-primary-subtle">Premium Student</span>
                                    @endif
                                </td>
                                <td>{{ $sub->start_date->translatedFormat('d M Y') }}</td>
                                <td>{{ $sub->end_date->translatedFormat('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
