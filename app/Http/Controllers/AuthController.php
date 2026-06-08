<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return Auth::user()->role === 'admin' 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            if ($user->is_suspended) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda telah ditangguhkan oleh Administrator.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang kembali, Admin!');
            }
            
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang di StudyPilot!');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang dimasukkan salah.',
        ])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'jurusan' => ['nullable', 'string', 'max:255'],
            'semester' => ['nullable', 'integer', 'min:1', 'max:14'],
            'education_level' => ['required', 'string', 'in:pelajar,mahasiswa,guru_dosen'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'jurusan' => $request->jurusan,
            'semester' => $request->semester,
            'education_level' => $request->education_level,
            'role' => 'user',
            'xp' => 0,
            'level' => 1,
            'streak' => 0,
        ]);

        // Berikan subscription Free secara otomatis
        Subscription::create([
            'user_id' => $user->id,
            'plan' => 'free',
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addYears(10), // Free plan selamanya
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Akun berhasil dibuat! Selamat datang di StudyPilot.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')->with('success', 'Anda telah berhasil keluar.');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak terdaftar di sistem kami.']);
        }

        // Simulasi Token Reset
        $token = Str::random(60);
        $request->session()->put('reset_token_' . $request->email, $token);

        return back()->with('success', 'Link reset password telah dikirim ke email Anda (Simulasi: token = ' . $token . '). Silakan gunakan formulir reset.');
    }

    public function showResetPasswordForm(Request $request)
    {
        return view('auth.reset-password', [
            'email' => $request->email,
            'token' => $request->token
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $savedToken = $request->session()->get('reset_token_' . $request->email);

        if (!$savedToken || $savedToken !== $request->token) {
            return back()->withErrors(['email' => 'Token reset password tidak valid atau kedaluwarsa.']);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update([
                'password' => Hash::make($request->password)
            ]);
            $request->session()->forget('reset_token_' . $request->email);
            return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan masuk dengan password baru Anda.');
        }

        return back()->withErrors(['email' => 'User tidak ditemukan.']);
    }

    public function editProfile()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'jurusan' => ['nullable', 'string', 'max:255'],
            'semester' => ['nullable', 'integer', 'min:1', 'max:14'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'theme_preference' => ['required', 'string', 'in:system,light,dark'],
            'education_level' => ['required', 'string', 'in:pelajar,mahasiswa,guru_dosen'],
        ]);

        $data = [
            'name' => $request->name,
            'jurusan' => $request->jurusan,
            'semester' => $request->semester,
            'theme_preference' => $request->theme_preference,
            'education_level' => $request->education_level,
        ];

        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $storageService = app(\App\Services\SupabaseStorageService::class);
            $fileUrl = $storageService->upload($file, 'avatars');
            
            // Hapus foto profil lama jika berupa file lokal
            if ($user->foto_profil && !str_starts_with($user->foto_profil, 'http') && file_exists(public_path($user->foto_profil))) {
                @unlink(public_path($user->foto_profil));
            }

            $data['foto_profil'] = $fileUrl;
        }

        // Update user
        User::where('id', $user->id)->update($data);

        return back()->with('success', 'Profil Anda berhasil diperbarui!');
    }
}
