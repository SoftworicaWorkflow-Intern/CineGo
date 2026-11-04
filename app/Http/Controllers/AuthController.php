<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    /**
     * Login form.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate the customer.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:160',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ], [
            'email.required' => ui('validation_email', 'Please enter your email address.'),
            'password.required' => ui('validation_password', 'Please enter your password.'),
        ]);

        $remember = (bool) ($validated['remember'] ?? false);

        if (! Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']], $remember)) {
            return back()
                ->withInput(['email' => $validated['email']])
                ->withErrors(['email' => ui('login_failed', 'Those details do not match our records.')]);
        }

        $request->session()->regenerate();

        $this->syncPreferences($request->user());

        return redirect()->intended(route('home'));
    }

    /**
     * Registration form.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Create the customer account and sign them in.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:160|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
            'city_id' => 'nullable|integer|exists:cities,id',
        ], [
            'name.required' => ui('validation_name', 'Please enter your name.'),
            'email.required' => ui('validation_email', 'Please enter your email address.'),
            'email.unique' => ui('email_taken', 'An account with this email already exists.'),
            'password.required' => ui('validation_password', 'Please choose a password.'),
            'password.confirmed' => ui('password_mismatch', 'The passwords do not match.'),
            'password.min' => ui('password_short', 'Password must be at least 8 characters.'),
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'city_id' => $validated['city_id'] ?? null,
            'preferred_language' => session('locale', 'en'),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', ui('welcome_cinego', 'Welcome to CineGo! Your account is ready.'));
    }

    /**
     * Sign out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Forgot password form.
     */
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Email a password reset link.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|max:160',
        ], [
            'email.required' => ui('validation_email', 'Please enter your email address.'),
            'email.email' => ui('validation_email_invalid', 'Please enter a valid email address.'),
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', ui('password_reset_sent', 'We have emailed your password reset link.'))
            : back()->withErrors(['email' => ui('no_account_email', 'We could not find an account with that email.')]);
    }

    /**
     * Reset password form.
     */
    public function showResetPassword(Request $request): View
    {
        return view('auth.reset-password', ['token' => $request->route('token')]);
    }

    /**
     * Save the new password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|max:160',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], [
            'password.required' => ui('validation_password', 'Please choose a password.'),
            'password.confirmed' => ui('password_mismatch', 'The passwords do not match.'),
            'password.min' => ui('password_short', 'Password must be at least 8 characters.'),
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', ui('password_reset_done', 'Your password has been reset. You can log in now.'))
            : back()->withErrors(['email' => ui('reset_token_invalid', 'This password reset link is invalid or has expired.')]);
    }

    private function syncPreferences(User $user): void
    {
        if (session('locale') === null && $user->preferred_language) {
            session(['locale' => $user->preferred_language]);
        }

        if (session('city_id') === null && $user->city_id) {
            session(['city_id' => $user->city_id]);
        }
    }
}
