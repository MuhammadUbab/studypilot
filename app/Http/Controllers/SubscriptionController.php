<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $activeSub = $user->activeSubscription;
        
        $currentPlan = $activeSub ? $activeSub->plan : 'free';
        $endDate = $activeSub ? $activeSub->end_date : null;

        return view('subscription.index', compact('currentPlan', 'endDate'));
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:free,premium,premium_plus',
            'billing_cycle' => 'nullable|string|in:monthly,yearly',
        ]);

        $user = Auth::user();
        $billingCycle = $request->input('billing_cycle', 'monthly');

        // Nonaktifkan subscription lama jika ada
        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        // Buat subscription baru
        if ($request->plan === 'free') {
            Subscription::create([
                'user_id' => $user->id,
                'plan' => 'free',
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addYears(10),
            ]);
            $msg = 'Masa berlangganan Anda telah dikembalikan ke paket Free.';
        } else {
            $endDate = $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth();
            Subscription::create([
                'user_id' => $user->id,
                'plan' => $request->plan,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => $endDate,
            ]);
            $planName = $request->plan === 'premium' ? 'Premium Student' : 'Premium Plus';
            $cycleName = $billingCycle === 'yearly' ? '1 Tahun' : '1 Bulan';
            $msg = "Pembayaran Berhasil! Akun Anda telah berhasil di-upgrade ke {$planName} ({$cycleName}). 🎉";
        }

        return redirect()->route('subscription.index')->with('success', $msg);
    }
}
