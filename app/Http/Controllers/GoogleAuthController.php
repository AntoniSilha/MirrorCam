<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $accountGoogle = \App\Models\AccountGoogle::firstOrCreate(
                ['id_user_google' => $googleUser->id],
                ['email' => $googleUser->email]
            );

            $user = \App\Models\User::where('id_user_google', $googleUser->id)->first();

            if ($user) {
                Auth::login($user);
                return redirect()->intended('/dashboard');
            } else {
                session([
                    'google_id' => $googleUser->id,
                    'google_email' => $googleUser->email,
                    'google_name' => $googleUser->name 
                ]);

                return redirect()->route('complete.profile');
            }

        }catch (\Exception $e) {
            return redirect('/login')->with('error', 'Terjadi kesalahan saat otentikasi Google.');
        }
    }

    public function storeDataProfile(Request $request)
    {
        if (!session()->has('google_id')) {
            return redirect('/login')->with('error', 'Sesi pendaftaran habis, silakan ulangi.');
        }

        $request->validate([
            'nama_lengkap_ktp' => 'required|string|max:255',
            'nik_ktp' => 'required|string|max:16|unique:users',
            'no_whatsapp' => 'required|string|max:20',
        ]);

        $user = User::create([
            'id_user_google' => session('google_id'),
            'role' => 'tenant',
            'nama_lengkap_ktp' => $request->nama_lengkap_ktp,
            'nik_ktp' => $request->nik_ktp,
            'no_whatsapp' => $request->no_whatsapp,
        ]);

        session()->forget(['google_id', 'google_email', 'google_name']);

        Auth::login($user);
        return redirect('/dashboard')->with('success', 'Pendaftaran berhasil!');
    }
}