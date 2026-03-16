<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        // Клиент
        if (Auth::guard('client')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard.client');
        }

        // Сотрудник
        if (Auth::guard('employee')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::guard('employee')->user();
            
            $redirectTo = match (strtolower(trim($user->role))) {
                'admin'      => route('dashboard.admin'),
                'doctor'     => route('dashboard.doctor'),
                'director'   => route('dashboard.director'),
                'accountant' => route('dashboard.accountant'),
                default      => route('dashboard.admin'),
            };
            
            return redirect($redirectTo);
        }

        return back()->withErrors([
            'email' => 'Неверный email или пароль.',
        ])->onlyInput('email');
    }
    public function destroy(Request $request)
    {
        Auth::guard('client')->logout();
        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}