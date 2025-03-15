<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $request->validated();
        $user = User::where('email', $request->email)
            ->where('no_telp', $request->no_telp)
            ->first();

        if (!$user) {
            return response()->json(["message" => "Bad Request", "errors" => ['message' => 'email belum terdaftar atau no telepon yang anda masukan salah']], 400);
        }
        $token = Password::createToken($user);
        return response()->json(['message' => 'Silakan atur password baru.', "data" => ["user" => ['email' => $user->email], 'token' => $token]], 200);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Bad Request', 'errors' => ['message' => 'User not found.']], 404);
        }

        $tokenExists = Password::tokenExists($user, $request->token);

        if ($tokenExists) {
            $user->update([
                'password' => bcrypt($data['password'])
            ]);
            return response()->json(['message' => 'Password berhasil diubah'], 200);
        } else {
            return response()->json(['message' => 'Unauthorize', 'errors' => ['message' => 'Token is invalid or expired.']], 400);
        }

    }
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Kirim email reset password
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Email reset password telah dikirim.'], 200)
            : response()->json(['message' => 'Email tidak ditemukan.'], 400);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Proses reset password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password berhasil diubah.'], 200)
            : response()->json(['message' => 'Token reset tidak valid atau kedaluwarsa.'], 400);
    }
}
