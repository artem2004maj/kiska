<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Главная страница
Route::get('/', [WelcomeController::class, 'index'])->name('home');

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
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// ====================== МАРШРУТЫ ДЛЯ ВЕРИФИКАЦИИ EMAIL ======================
Route::middleware('auth:client')->group(function () {
    Route::get('/email/verify', function () {
        return Inertia::render('Auth/VerifyEmail', [
            'status' => session('status'),
        ]);
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('dashboard.client')->with('message', 'Email успешно подтвержден!');
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('status', 'Ссылка для подтверждения отправлена!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

// ====================== КЛИЕНТЫ ======================
Route::middleware('auth:client')->group(function () {
    // Главная
    Route::get('/dashboard/client', [App\Http\Controllers\Client\DashboardController::class, 'index'])
        ->name('dashboard.client');
    
    // Остальные страницы клиента
    Route::get('/client/services', [App\Http\Controllers\Client\DashboardController::class, 'services'])
        ->name('client.services');
    
    Route::get('/client/appointments', [App\Http\Controllers\Client\DashboardController::class, 'appointments'])
        ->name('client.appointments');
    
    Route::get('/client/history', [App\Http\Controllers\Client\DashboardController::class, 'history'])
        ->name('client.history');
    
    Route::get('/client/medical-records', [App\Http\Controllers\Client\DashboardController::class, 'medicalRecords'])
        ->name('client.medical-records');
    
    Route::get('/client/profile', [App\Http\Controllers\Client\DashboardController::class, 'profile'])
        ->name('client.profile');
    
    // API маршруты для клиента
    Route::prefix('api/client')->name('client.api.')->group(function () {
        Route::get('/available-slots', [App\Http\Controllers\Client\DashboardController::class, 'getAvailableSlots']);
        Route::post('/appointments', [App\Http\Controllers\Client\DashboardController::class, 'createAppointment']);
        Route::put('/appointments/{id}/cancel', [App\Http\Controllers\Client\DashboardController::class, 'cancelAppointment']);
        Route::put('/appointments/{id}/reschedule', [App\Http\Controllers\Client\DashboardController::class, 'rescheduleAppointment']);
        Route::post('/feedback', [App\Http\Controllers\Client\DashboardController::class, 'leaveFeedback']);
        Route::put('/profile', [App\Http\Controllers\Client\DashboardController::class, 'updateProfile']);
        Route::post('/upload-photo', [App\Http\Controllers\Client\DashboardController::class, 'uploadPhoto']);
        Route::delete('/delete-photo', [App\Http\Controllers\Client\DashboardController::class, 'deletePhoto']);
        Route::put('/notifications/{id}/read', [App\Http\Controllers\Client\DashboardController::class, 'markNotificationAsRead']);
        Route::put('/notifications/read-all', [App\Http\Controllers\Client\DashboardController::class, 'markAllNotificationsAsRead']);
    });
});

// ====================== СОТРУДНИКИ ======================
Route::middleware('auth:employee')->group(function () {
    // Админ
    Route::get('/dashboard/admin', fn() => Inertia::render('Dashboard/Admin'))
        ->name('dashboard.admin');
    
    // Доктор - главная страница с графиком
    Route::get('/dashboard/doctor', [DoctorDashboardController::class, 'index'])
        ->name('dashboard.doctor');
    
    // Маршруты для доктора
    Route::get('/doctor/patients', [DoctorDashboardController::class, 'patients'])
        ->name('doctor.patients');
    
    Route::get('/doctor/services', [DoctorDashboardController::class, 'services'])
        ->name('doctor.services');
    
    Route::get('/doctor/profile', [DoctorDashboardController::class, 'profile'])
        ->name('doctor.profile');
    
    Route::get('/doctor/medical-records/{clientId}', [DoctorDashboardController::class, 'medicalRecord'])
        ->name('doctor.medical-record');

    
    
    // API маршруты для доктора
    Route::prefix('api/doctor')->name('doctor.api.')->group(function () {
        Route::get('/appointments', [DoctorDashboardController::class, 'getAppointments']);
        Route::get('/appointments/{id}', [DoctorDashboardController::class, 'getAppointment']);
        Route::put('/appointments/{id}/status', [DoctorDashboardController::class, 'updateStatus']);
        Route::post('/appointments/{id}/materials', [DoctorDashboardController::class, 'addMaterial']);
        Route::post('/appointments/{id}/materials/save', [DoctorDashboardController::class, 'saveAppointmentMaterials']);
        Route::post('/appointments/{id}/complete', [DoctorDashboardController::class, 'completeAppointment']);
        Route::get('/patients/search', [DoctorDashboardController::class, 'searchPatients']);
        Route::get('/materials/available', [DoctorDashboardController::class, 'getAvailableMaterials']);
        Route::put('/profile', [DoctorDashboardController::class, 'updateProfile']);
        Route::post('/medical-records/{clientId}', [DoctorDashboardController::class, 'saveMedicalRecord']);
        Route::put('/medical-records/{recordId}', [DoctorDashboardController::class, 'updateMedicalRecord']);
        // API для фото:
        Route::post('/upload-photo', [DoctorDashboardController::class, 'uploadPhoto']);
        Route::delete('/delete-photo', [DoctorDashboardController::class, 'deletePhoto']);
        Route::put('/appointments/{id}/confirm', [DoctorDashboardController::class, 'confirmAppointment']);
    });
    
    // Директор
    Route::get('/dashboard/director', fn() => Inertia::render('Dashboard/Director'))
        ->name('dashboard.director');
    
    // ====================== БУХГАЛТЕР ======================
    Route::middleware('auth:employee')->group(function () {
        // Бухгалтер - главная страница
        Route::get('/dashboard/accountant', [App\Http\Controllers\Accountant\DashboardController::class, 'index'])
            ->name('dashboard.accountant');
        
        // Остальные страницы бухгалтера
        Route::get('/accountant/payments', [App\Http\Controllers\Accountant\DashboardController::class, 'payments'])
            ->name('accountant.payments');
        
        Route::get('/accountant/warehouse', [App\Http\Controllers\Accountant\DashboardController::class, 'warehouse'])
            ->name('accountant.warehouse');
        
        Route::get('/accountant/salary', [App\Http\Controllers\Accountant\DashboardController::class, 'salary'])
            ->name('accountant.salary');
        // В секцию бухгалтера добавьте:
        Route::get('/accountant/suppliers', [App\Http\Controllers\Accountant\DashboardController::class, 'suppliers'])
            ->name('accountant.suppliers');

        Route::post('/api/accountant/suppliers', [App\Http\Controllers\Accountant\DashboardController::class, 'createSupplier']);
        Route::put('/api/accountant/suppliers/{id}', [App\Http\Controllers\Accountant\DashboardController::class, 'updateSupplier']);
        Route::delete('/api/accountant/suppliers/{id}', [App\Http\Controllers\Accountant\DashboardController::class, 'deleteSupplier']);
        // В секцию бухгалтера добавьте:
        Route::get('/accountant/profile', [App\Http\Controllers\Accountant\DashboardController::class, 'profile'])
            ->name('accountant.profile');

        Route::put('/api/accountant/profile', [App\Http\Controllers\Accountant\DashboardController::class, 'updateProfile']);
        Route::post('/api/accountant/upload-photo', [App\Http\Controllers\Accountant\DashboardController::class, 'uploadPhoto']);
        Route::delete('/api/accountant/delete-photo', [App\Http\Controllers\Accountant\DashboardController::class, 'deletePhoto']);
        // API маршруты для бухгалтера
        Route::prefix('api/accountant')->name('accountant.api.')->group(function () {
            Route::post('/payments/{id}/accept', [App\Http\Controllers\Accountant\DashboardController::class, 'acceptPayment']);
            Route::post('/orders/create', [App\Http\Controllers\Accountant\DashboardController::class, 'createOrder']);
            Route::post('/orders/{id}/receive', [App\Http\Controllers\Accountant\DashboardController::class, 'receiveOrder']);
            Route::post('/salary/calculate', [App\Http\Controllers\Accountant\DashboardController::class, 'calculateSalary']);
            Route::post('/salary/pay', [App\Http\Controllers\Accountant\DashboardController::class, 'paySalary']);
            Route::post('/salary/pay-all', [App\Http\Controllers\Accountant\DashboardController::class, 'payAllSalaries']);
            Route::get('/revenue-stats', [App\Http\Controllers\Accountant\DashboardController::class, 'getRevenueStats']);
        });
    });
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


// Выход
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

// Fallback
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