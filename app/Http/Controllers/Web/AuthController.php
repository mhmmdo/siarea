<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $credentials['login'];

        // Determine if loginInput is a phone number (numeric)
        $user = null;
        if (is_numeric($loginInput)) {
            // Find employee with this phone
            $employee = \App\Models\Employee::where('phone', $loginInput)
                ->where('status', 'active')
                ->first();
            if ($employee) {
                $user = $employee->user;
            }
        } else {
            // Find user with this username or email (for admin)
            $user = User::where(function($query) use ($loginInput) {
                $query->where('username', $loginInput)
                      ->orWhere('email', $loginInput);
            })
            ->where('status', 'active')
            ->first();
        }

        if (!$user || !password_verify($credentials['password'], $user->password)) {
            return back()->withErrors([
                'login' => 'Nomor HP atau password salah',
            ])->onlyInput('login');
        }

        Auth::login($user, $request->has('remember'));

        // Redirect based on role
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('user.dashboard');
        }
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
