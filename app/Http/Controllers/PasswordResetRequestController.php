<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetRequestController extends Controller
{
    /** Buat password sementara dan tandai permintaan selesai. */
    public function approve(Request $request, PasswordResetRequest $passwordRequest)
    {
        $this->pastikanSuperAdmin($request);
        abort_unless($passwordRequest->status === 'pending', 404);

        $user = $passwordRequest->user;
        $passwordSementara = Str::random(10);

        $user->forceFill([
            'password'       => Hash::make($passwordSementara),
            'remember_token' => Str::random(60), // putuskan sesi "ingat saya" yang lama
        ])->save();

        $passwordRequest->update([
            'status'     => 'done',
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('temp_password', $passwordSementara)
            ->with('temp_user', $user->name);
    }

    /** Tolak permintaan tanpa mengubah password. */
    public function reject(Request $request, PasswordResetRequest $passwordRequest)
    {
        $this->pastikanSuperAdmin($request);
        abort_unless($passwordRequest->status === 'pending', 404);

        $passwordRequest->update([
            'status'     => 'rejected',
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return redirect()->route('dashboard');
    }

    private function pastikanSuperAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'super_admin', 403);
    }
}