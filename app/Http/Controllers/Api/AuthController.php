<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Disparar evento de registro para enviar email de verificación
        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'User registered successfully. Please check your email to verify your account.',
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, (string) $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
                'errors' => ['email' => ['The provided credentials are incorrect.']]
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'Login successful',
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'errors' => null
            ], 404);
        }

        // Verificar que el hash coincida con el email del usuario
        $expectedHash = sha1($user->email);
        $providedHash = $request->route('hash');

        if ($expectedHash !== $providedHash) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link',
                'errors' => null
            ], 400);
        }

        // El middleware 'signed' ya validó la firma y expiración
        // Si llegamos aquí, la URL es válida

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Email already verified'
            ], 200);
        }

        if ($user->markEmailAsVerified()) {
            if ($user instanceof MustVerifyEmail) {
                event(new Verified($user));
            }
        }

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Email verified successfully'
        ], 200);
    }

    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Email already verified'
            ], 200);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Verification email sent'
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Successfully logged out'
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
            'message' => 'User data retrieved successfully'
        ]);
    }

    /**
     * Enviar enlace de reset de contraseña
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el usuario existe
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'We can\'t find a user with that email address.',
                'errors' => ['email' => ['We can\'t find a user with that email address.']]
            ], 404);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Password reset link sent to your email address'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to send reset link',
            'errors' => ['email' => ['Unable to send reset link']]
        ], 500);
    }

    /**
     * Resetear la contraseña
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Password has been reset successfully'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to reset password',
            'errors' => ['email' => [__($status)]]
        ], 400);
    }
}