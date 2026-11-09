<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        // Catat permintaan hanya jika akun ada dan belum ada permintaan yang menunggu
        if ($user && ! PasswordResetRequest::where('user_id', $user->id)->where('status', 'pending')->exists()) {
            PasswordResetRequest::create(['user_id' => $user->id]);
        }

        // Pesan sama untuk email terdaftar maupun tidak, agar tidak membocorkan daftar akun
        return back()->with('status', 'Permintaan reset password telah dikirim ke admin. Silakan hubungi admin untuk mendapatkan password sementara.');
    }
}