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
            'login'    => 'required|string',  // меняем email на login
            'password' => 'required|string',
        ]);

        $login = $request->login;
        $password = $request->password;
        $remember = $request->boolean('remember');

        // Определяем, что ввел пользователь: email или логин
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'login';

        // Пытаемся авторизовать как клиента
        $clientCredentials = [$field => $login, 'password' => $password];
        if (Auth::guard('client')->attempt($clientCredentials, $remember)) {
            $request->session()->regenerate();
            Log::info('Client logged in', ['login' => $login]);
            return redirect()->intended(route('dashboard.client'));
        }

        // Пытаемся авторизовать как сотрудника
        $employeeCredentials = [$field => $login, 'password' => $password];
        if (Auth::guard('employee')->attempt($employeeCredentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::guard('employee')->user();
            Log::info('Employee logged in', ['login' => $login, 'role' => $user->role]);
            return redirect()->intended($this->getRedirectRouteByRole($user->role));
        }

        Log::warning('Failed login attempt', ['login' => $login]);
        
        return back()->withErrors([
            'login' => 'Неверный логин/email или пароль.',
        ])->onlyInput('login');
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
            'director'   => route('director.dashboard'),
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