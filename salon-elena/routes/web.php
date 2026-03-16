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
// В секцию клиентов добавьте:
Route::middleware('auth:client')->group(function () {
    Route::get('/dashboard/client', [App\Http\Controllers\Client\DashboardController::class, 'index'])
        ->name('dashboard.client');
    
    // API маршруты для клиента
    Route::prefix('api/client')->group(function () {
        Route::put('/appointments/{id}/cancel', function($id) {
            // Логика отмены
        });
        
        Route::put('/profile', function(Request $request) {
            // Логика обновления профиля
        });
    });
});

// ====================== СОТРУДНИКИ ======================
Route::middleware('auth:employee')->group(function () {
    Route::get('/dashboard/admin', fn() => Inertia::render('Dashboard/Admin'))
        ->name('dashboard.admin');

    Route::get('/dashboard/doctor', fn() => Inertia::render('Dashboard/Doctor'))
        ->name('dashboard.doctor');

    Route::get('/dashboard/director', fn() => Inertia::render('Dashboard/Director'))
        ->name('dashboard.director');

    Route::get('/dashboard/accountant', fn() => Inertia::render('Dashboard/Accountant'))
        ->name('dashboard.accountant');
});

Route::get('/debug-employee', function() {
    $employee = \App\Models\Employee::where('email', 'doctor@example.com')->first();
    
    return response()->json([
        'employee_found' => $employee ? true : false,
        'employee_data' => $employee ? [
            'employee_id' => $employee->employee_id,
            'email' => $employee->email,
            'role' => $employee->role,
            'login' => $employee->login,
        ] : null,
        'auth_check' => [
            'client' => Auth::guard('client')->check(),
            'employee' => Auth::guard('employee')->check(),
            'web' => Auth::guard('web')->check(),
        ],
        'session' => [
            'id' => session()->getId(),
            'all' => session()->all(),
        ],
    ]);
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