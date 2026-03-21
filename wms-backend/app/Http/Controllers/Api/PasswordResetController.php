<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\PasswordResetService;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function __construct(private readonly PasswordResetService $passwordResetService)
    {
    }

    public function forgot(ForgotPasswordRequest $request)
    {
        // Always return the same response to avoid leaking which emails exist.
        $this->passwordResetService->sendResetLink($request->validated('email'));

        return response()->json([
            'success' => true,
            'message' => 'link đặt lại mật khẩu đã được gửi.',
        ]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $status = $this->passwordResetService->resetPassword(
            $request->input('email'),
            $request->input('token'),
            $request->input('password'),
            $request->input('password_confirmation')
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập lại.',
        ]);
    }
}
