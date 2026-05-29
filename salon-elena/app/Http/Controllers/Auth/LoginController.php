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
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
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
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->login;
        $password = $request->password;
        $remember = $request->boolean('remember');

        // Определяем тип введенных данных (email, логин или телефон)
        $field = $this->identifyLoginField($login);

        // Пытаемся авторизовать как клиента
        $clientCredentials = [$field => $login, 'password' => $password];
        if (Auth::guard('client')->attempt($clientCredentials, $remember)) {
            $request->session()->regenerate();
            Log::info('Client logged in', ['login' => $login]);
            return redirect()->intended(route('client.dashboard'));
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
            'login' => 'Неверный логин/email/телефон или пароль.',
        ])->onlyInput('login');
    }
    
    /**
     * Определяет, по какому полю искать пользователя
     * Возвращает: 'email', 'login', 'phone'
     */
    protected function identifyLoginField($login)
    {
        // Проверка на email
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }
        
        // Проверка на телефон (только цифры, 10-11 цифр)
        $cleanPhone = preg_replace('/[^0-9]/', '', $login);
        if (strlen($cleanPhone) >= 10 && strlen($cleanPhone) <= 11) {
            return 'phone';
        }
        
        // По умолчанию - логин
        return 'login';
    }
    
    protected function getRedirectRouteByRole($role)
    {
        $role = strtolower(trim($role));
        
        return match ($role) {
            'doctor'     => route('dashboard.doctor'),
            'director'   => route('director.dashboard'),
            'accountant' => route('dashboard.accountant'),
            'admin'      => route('admin.dashboard'),
            default      => route('login'),
        };
    }
    
    public function destroy(Request $request)
    {
        if (Auth::guard('client')->check()) {
            Log::info('Client logged out', ['email' => Auth::guard('client')->user()->email]);
        }
        if (Auth::guard('employee')->check()) {
            Log::info('Employee logged out', [
                'email' => Auth::guard('employee')->user()->email,
                'role' => Auth::guard('employee')->user()->role
            ]);
        }
        
        Auth::guard('client')->logout();
        Auth::guard('employee')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}