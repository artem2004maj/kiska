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
        // Если уже авторизован, редиректим на соответствующий дашборд
        if (Auth::guard('client')->check()) {
            return redirect()->route('dashboard.client');
        }
        
        if (Auth::guard('employee')->check()) {
            $user = Auth::guard('employee')->user();
            return redirect($this->getRedirectRouteByRole($user->role));
        }
        
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
            'canReset' => false,
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

        // Пытаемся авторизовать как клиента
        if (Auth::guard('client')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Логируем успешный вход клиента
            Log::info('Client logged in', ['email' => $credentials['email']]);
            
            return redirect()->intended(route('dashboard.client'));
        }

        // Пытаемся авторизовать как сотрудника
        if (Auth::guard('employee')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::guard('employee')->user();
            
            // Логируем успешный вход сотрудника
            Log::info('Employee logged in', [
                'email' => $credentials['email'],
                'role' => $user->role
            ]);
            
            // Используем intended для редиректа на запрашиваемую страницу
            // или на дашборд по роли
            return redirect()->intended($this->getRedirectRouteByRole($user->role));
        }

        // Если не удалось авторизоваться
        Log::warning('Failed login attempt', ['email' => $credentials['email']]);
        
        return back()->withErrors([
            'email' => 'Неверный email или пароль.',
        ])->onlyInput('email');
    }
    
    /**
     * Получить маршрут для редиректа на основе роли сотрудника
     */
    protected function getRedirectRouteByRole($role)
    {
        // Приводим к нижнему регистру и убираем пробелы
        $role = strtolower(trim($role));
        
        return match ($role) {
            'admin'      => route('dashboard.admin'),
            'doctor'     => route('dashboard.doctor'),
            'director'   => route('dashboard.director'),
            'accountant' => route('dashboard.accountant'),
            default      => route('dashboard.admin'), // По умолчанию админка
        };
    }
    
    public function destroy(Request $request)
    {
        // Логируем выход
        if (Auth::guard('client')->check()) {
            Log::info('Client logged out', ['email' => Auth::guard('client')->user()->email]);
        }
        if (Auth::guard('employee')->check()) {
            Log::info('Employee logged out', [
                'email' => Auth::guard('employee')->user()->email,
                'role' => Auth::guard('employee')->user()->role
            ]);
        }
        
        // Выход из всех guards
        Auth::guard('client')->logout();
        Auth::guard('employee')->logout();
        
        // Очищаем сессию
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}