<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Главная страница
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/check-auth', function() {
    return response()->json([
        'client' => [
            'check' => Auth::guard('client')->check(),
            'user' => Auth::guard('client')->user() ? Auth::guard('client')->user()->email : null,
        ],
        'employee' => [
            'check' => Auth::guard('employee')->check(),
            'user' => Auth::guard('employee')->user() ? Auth::guard('employee')->user()->email : null,
        ],
        'session' => [
            'id' => session()->getId(),
            'all' => session()->all(),
        ],
    ]);
})->name('check.auth');

// ====================== ГОСТИ ======================
Route::middleware('guest')->group(function () {
    // Единый логин
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store']);

    // Регистрация клиента
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// ====================== КЛИЕНТЫ ======================
Route::middleware('auth:client')->group(function () {
    Route::get('/dashboard/client', fn() => Inertia::render('Dashboard/Client'))
        ->name('dashboard.client');
    
    // Другие маршруты для клиентов
});

// ====================== СОТРУДНИКИ ======================
Route::middleware('auth')->group(function () {
    Route::get('/dashboard/admin', fn() => Inertia::render('Dashboard/Admin'))
        ->name('dashboard.admin');

    Route::get('/dashboard/doctor', fn() => Inertia::render('Dashboard/Doctor'))
        ->name('dashboard.doctor');

    Route::get('/dashboard/director', fn() => Inertia::render('Dashboard/Director'))
        ->name('dashboard.director');

    Route::get('/dashboard/accountant', fn() => Inertia::render('Dashboard/Accountant'))
        ->name('dashboard.accountant');
    
    // Другие маршруты для сотрудников
});

// Выход (доступен всем аутентифицированным)
Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout');

// Временный fallback (самый надёжный)
Route::get('/dashboard', function () {
    if (Auth::guard('client')->check()) {
        return redirect()->route('dashboard.client');
    }

    if (Auth::guard('employee')->check()) {
        $user = Auth::guard('employee')->user();
        $roleRoute = 'dashboard.' . ($user->role ?? 'admin');
        return redirect()->route($roleRoute);
    }

    return redirect()->route('login');
})->name('dashboard');