@extends('layouts.app')

@section('title', 'Pricing & Upgrade')

@section('content')
<div class="container-fluid py-4" style="max-width: 1200px; margin: 0 auto;">
    <div class="row mb-4 text-center">
        <div class="col-12">
            <h1 class="display-6 fw-bold text-white mb-2">Upgrade Keanggotaan StudyPilot</h1>
            <p class="text-secondary">Pilih paket terbaik untuk menunjang produktivitas dan kesuksesan akademik Anda.</p>
            
            <div class="mt-3">
                <span class="badge bg-indigo-subtle text-primary px-4 py-2.5 rounded-pill fs-6 border border-primary-subtle" style="background-color: var(--color-primary-glow);">
                    Status Paket Aktif Anda: <strong class="text-uppercase text-white ms-1">{{ $currentPlan === 'premium_plus' ? 'Premium Plus' : ($currentPlan === 'premium' ? 'Premium Student' : 'Free') }}</strong>
                </span>
                @if($endDate && $currentPlan !== 'free')
                    <span class="text-secondary small d-block mt-2">Masa aktif hingga: <strong class="text-white">{{ $endDate->translatedFormat('d M Y') }}</strong></span>
                @endif
            </div>
        </div>
    </div>

    <!-- Monthly / Annual Toggle -->
    <div class="d-flex justify-content-center align-items-center gap-3 mb-5">
        <span class="fw-semibold text-white" id="label-monthly">Bulanan</span>
        <div class="form-check form-switch fs-4 mb-0">
            <input class="form-check-input" type="checkbox" role="switch" id="billingCycleToggle" style="cursor: pointer; background-color: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2);">
        </div>
        <span class="fw-semibold text-secondary" id="label-yearly">Tahunan 
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fs-7 ms-1 px-2.5 py-1">Hemat 20%</span>
        </span>
    </div>

    <!-- Pricing Cards Grid -->
    <div class="row g-4 justify-content-center mb-5">
        <!-- Free Plan -->
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 d-flex flex-column position-relative {{ $currentPlan === 'free' ? 'border border-secondary' : '' }}" style="{{ $currentPlan === 'free' ? 'background: linear-gradient(135deg, rgba(31, 41, 55, 0.4) 0%, rgba(17, 24, 39, 0.8) 100%);' : '' }}">
                @if($currentPlan === 'free')
                    <span class="position-absolute top-0 end-0 bg-secondary text-white px-3 py-1 rounded-bl-lg small fw-bold" style="border-bottom-left-radius: 12px; border-top-right-radius: 15px;">Aktif</span>
                @endif
                <div class="mb-3">
                    <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill mb-2">Free</span>
                    <h3 class="text-white mb-0 fw-bold">Rp0</h3>
                    <p class="text-secondary small mt-2">Paket standar untuk semua pendaftar baru.</p>
                </div>
                
                <hr class="border-secondary-subtle my-3">

                <ul class="list-unstyled mb-5 flex-grow-1 small">
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Maksimal 10 tugas aktif</li>
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> 3 ringkasan AI per hari</li>
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> 3 kuis AI per hari</li>
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> 1 study planner per hari</li>
                    <li class="mb-3 text-muted"><i class="fa-solid fa-circle-xmark text-danger me-2"></i> Chat materi kuliah RAG</li>
                    <li class="mb-3 text-muted"><i class="fa-solid fa-circle-xmark text-danger me-2"></i> AI Exam Predictor</li>
                </ul>
                
                @if($currentPlan === 'free')
                    <button class="btn btn-outline-light w-100 py-2.5 mt-auto" disabled><i class="fa-solid fa-check me-2"></i> Paket Aktif Anda</button>
                @else
                    <form action="{{ route('subscription.upgrade') }}" method="POST" class="mt-auto">
                        @csrf
                        <input type="hidden" name="plan" value="free">
                        <button type="submit" class="btn btn-outline-light w-100 py-2.5">Kembali ke Paket Free</button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Premium Student Plan -->
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 d-flex flex-column position-relative" style="{{ $currentPlan === 'premium' ? 'border: 2px solid var(--color-primary) !important; background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(17, 24, 39, 0.8) 100%);' : '' }}">
                @if($currentPlan === 'premium')
                    <span class="position-absolute top-0 end-0 bg-primary text-white px-3 py-1 rounded-bl-lg small fw-bold" style="border-bottom-left-radius: 12px; border-top-right-radius: 15px;">Aktif</span>
                @endif
                <div class="mb-3">
                    <span class="badge bg-indigo-subtle text-primary px-3 py-1 rounded-pill mb-2" style="background-color: var(--color-primary-glow);">Premium Student</span>
                    <h3 class="text-white mb-0 fw-bold"><span id="price-premium-student">Rp24.900</span> <span class="fs-6 text-secondary" id="label-premium-student">/bln</span></h3>
                    <p class="text-secondary small mt-2">Fitur lengkap untuk kebutuhan belajar harian Anda.</p>
                </div>
                
                <hr class="border-secondary-subtle my-3">

                <ul class="list-unstyled mb-5 flex-grow-1 small">
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Tugas aktif tanpa batas</li>
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Ringkasan & Kuis tanpa batas</li>
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Chat materi AI tanpa batas</li>
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Study planner harian tanpa batas</li>
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Analitik akademik lengkap</li>
                    <li class="mb-3 text-muted"><i class="fa-solid fa-circle-xmark text-danger me-2"></i> AI Exam Predictor</li>
                </ul>
                
                @if($currentPlan === 'premium')
                    <button class="btn btn-primary w-100 py-2.5 mt-auto" disabled><i class="fa-solid fa-check me-2"></i> Paket Aktif Anda</button>
                @else
                    <button class="btn btn-primary w-100 py-2.5 mt-auto" onclick="openPaymentModal('premium')"><i class="fa-solid fa-circle-arrow-up me-2"></i> Upgrade Sekarang</button>
                @endif
            </div>
        </div>

        <!-- Premium Plus Plan -->
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 d-flex flex-column position-relative" style="{{ $currentPlan === 'premium_plus' ? 'border: 2px solid var(--color-secondary) !important; background: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(17, 24, 39, 0.8) 100%);' : '' }}">
                @if($currentPlan === 'premium_plus')
                    <span class="position-absolute top-0 end-0 bg-success text-white px-3 py-1 rounded-bl-lg small fw-bold" style="border-bottom-left-radius: 12px; border-top-right-radius: 15px;">Aktif</span>
                @else
                    <span class="position-absolute top-0 end-0 bg-warning text-dark px-3 py-1 rounded-bl-lg small fw-bold" style="border-bottom-left-radius: 12px; border-top-right-radius: 15px; background-color: var(--color-warning) !important;">Terpopuler</span>
                @endif
                <div class="mb-3">
                    <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill mb-2" style="background-color: rgba(139, 92, 246, 0.15); color: var(--color-secondary) !important;">Premium Plus</span>
                    <h3 class="text-white mb-0 fw-bold"><span id="price-premium-plus">Rp39.900</span> <span class="fs-6 text-secondary" id="label-premium-plus">/bln</span></h3>
                    <p class="text-secondary small mt-2">Akses super eksklusif untuk persiapan ujian maksimal.</p>
                </div>
                
                <hr class="border-secondary-subtle my-3">

                <ul class="list-unstyled mb-5 flex-grow-1 small">
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Semua fitur Premium Student</li>
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> AI Exam Predictor</li>
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Exam Readiness Score</li>
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Prioritas antrean proses AI</li>
                    <li class="mb-3 text-light"><i class="fa-solid fa-circle-check text-success me-2"></i> Insight akademik mendalam</li>
                </ul>
                
                @if($currentPlan === 'premium_plus')
                    <button class="btn btn-outline-light w-100 py-2.5 mt-auto" disabled><i class="fa-solid fa-check me-2"></i> Paket Aktif Anda</button>
                @else
                    <button class="btn btn-outline-light w-100 py-2.5 mt-auto text-white" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); border: none;" onclick="openPaymentModal('premium_plus')"><i class="fa-solid fa-gem me-2"></i> Upgrade Plus</button>
                @endif
            </div>
        </div>
    </div>

    <!-- Feature Comparison Table -->
    <div class="glass-card p-4 mt-5">
        <h4 class="fw-bold mb-4 text-center text-white"><i class="fa-solid fa-table-list text-primary me-2"></i> Tabel Perbandingan Fitur</h4>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle border border-secondary-subtle mb-0" style="border-radius:12px; overflow:hidden;">
                <thead>
                    <tr class="table-dark-header">
                        <th scope="col" style="width: 40%;" class="ps-4">Fitur Utama</th>
                        <th scope="col" class="text-center" style="width: 20%;">Free</th>
                        <th scope="col" class="text-center" style="width: 20%;">Premium Student</th>
                        <th scope="col" class="text-center" style="width: 20%;">Premium Plus</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="ps-4">Maksimal Tugas Aktif</td>
                        <td class="text-center text-secondary">10 Tugas</td>
                        <td class="text-center text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Tanpa Batas</td>
                        <td class="text-center text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Tanpa Batas</td>
                    </tr>
                    <tr>
                        <td class="ps-4">AI Note & Summary (Materi)</td>
                        <td class="text-center text-secondary">3 Dokumen / hari</td>
                        <td class="text-center text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Tanpa Batas</td>
                        <td class="text-center text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Tanpa Batas</td>
                    </tr>
                    <tr>
                        <td class="ps-4">AI Quiz Generator</td>
                        <td class="text-center text-secondary">3 Kuis / hari</td>
                        <td class="text-center text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Tanpa Batas</td>
                        <td class="text-center text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Tanpa Batas</td>
                    </tr>
                    <tr>
                        <td class="ps-4">AI Study Planner</td>
                        <td class="text-center text-secondary">1 Rencana / hari</td>
                        <td class="text-center text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Tanpa Batas</td>
                        <td class="text-center text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Tanpa Batas</td>
                    </tr>
                    <tr>
                        <td class="ps-4">AI Chat Materi (RAG)</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> Tidak Tersedia</td>
                        <td class="text-center text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Tanpa Batas</td>
                        <td class="text-center text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Tanpa Batas</td>
                    </tr>
                    <tr>
                        <td class="ps-4">AI Exam Predictor & Kisi-Kisi</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> Tidak Tersedia</td>
                        <td class="text-center text-danger"><i class="fa-solid fa-circle-xmark"></i> Tidak Tersedia</td>
                        <td class="text-center text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Akses Penuh</td>
                    </tr>
                    <tr>
                        <td class="ps-4">Prioritas Antrean Server AI</td>
                        <td class="text-center text-secondary">Standar</td>
                        <td class="text-center text-secondary">Standar</td>
                        <td class="text-center text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Utama & Instan</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Mock Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-white" style="border-radius:16px;">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Simulasi Pembayaran (Mock Payment)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fa-solid fa-credit-card text-primary display-4 mb-3" style="color:var(--color-primary);"></i>
                <h5 class="mb-1 text-white">Konfirmasi Upgrade Keanggotaan</h5>
                <p class="text-secondary small mb-4" id="payment-text">Anda akan membeli paket seharga Rp0.</p>
                
                <div class="p-3 rounded-3 mb-4 text-start" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color);">
                    <div class="d-flex justify-content-between mb-2 small text-secondary">
                        <span>Penyedia Gateway</span>
                        <span class="text-white">MockPay MVP</span>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary">
                        <span>Status Keamanan</span>
                        <span class="text-success"><i class="fa-solid fa-shield-halved me-1"></i> Sandbox Securer</span>
                    </div>
                </div>

                <form action="{{ route('subscription.upgrade') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" id="payment-plan-input">
                    <input type="hidden" name="billing_cycle" id="payment-billing-cycle-input" value="monthly">
                    <button type="submit" class="btn btn-primary w-100 py-2.5 mb-2"><i class="fa-solid fa-shield-heart me-1"></i> Simulasikan Bayar Sukses</button>
                    <button type="button" class="btn btn-outline-light w-100" data-bs-dismiss="modal">Batalkan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Toggle Billing Cycle
    document.getElementById('billingCycleToggle').addEventListener('change', function() {
        const isYearly = this.checked;
        const billingCycleInput = document.getElementById('payment-billing-cycle-input');
        
        const priceStudent = document.getElementById('price-premium-student');
        const pricePlus = document.getElementById('price-premium-plus');
        const labelStudent = document.getElementById('label-premium-student');
        const labelPlus = document.getElementById('label-premium-plus');
        
        const labelMonthly = document.getElementById('label-monthly');
        const labelYearly = document.getElementById('label-yearly');

        if (isYearly) {
            priceStudent.innerText = 'Rp19.900';
            labelStudent.innerHTML = '/bln <span class="d-block text-secondary" style="font-size: 0.7rem;">(Ditagih Rp238.800/thn)</span>';
            pricePlus.innerText = 'Rp31.900';
            labelPlus.innerHTML = '/bln <span class="d-block text-secondary" style="font-size: 0.7rem;">(Ditagih Rp382.800/thn)</span>';
            
            labelYearly.classList.replace('text-secondary', 'text-white');
            labelMonthly.classList.replace('text-white', 'text-secondary');
        } else {
            priceStudent.innerText = 'Rp24.900';
            labelStudent.innerText = '/bln';
            pricePlus.innerText = 'Rp39.900';
            labelPlus.innerText = '/bln';
            
            labelMonthly.classList.replace('text-secondary', 'text-white');
            labelYearly.classList.replace('text-white', 'text-secondary');
        }
    });

    function openPaymentModal(plan) {
        const isYearly = document.getElementById('billingCycleToggle').checked;
        const cycleText = isYearly ? 'tahunan (12 bulan)' : 'bulanan (1 bulan)';
        const cycleVal = isYearly ? 'yearly' : 'monthly';
        
        let price = 'Rp24.900';
        let planText = 'Premium Student';

        if (plan === 'premium') {
            price = isYearly ? 'Rp19.900/bln (Total Rp238.800)' : 'Rp24.900/bln';
            planText = 'Premium Student';
        } else if (plan === 'premium_plus') {
            price = isYearly ? 'Rp31.900/bln (Total Rp382.800)' : 'Rp39.900/bln';
            planText = 'Premium Plus';
        }

        document.getElementById('payment-text').innerText = `Anda akan membeli paket ${planText} secara ${cycleText} seharga ${price}.`;
        document.getElementById('payment-plan-input').value = plan;
        document.getElementById('payment-billing-cycle-input').value = cycleVal;
        
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    }
</script>
@endsection
