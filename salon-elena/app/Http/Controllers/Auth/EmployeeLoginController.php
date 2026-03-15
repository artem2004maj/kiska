<?php

// namespace App\Http\Controllers\Auth;

// use Illuminate\Support\Facades\Hash;
// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Inertia\Inertia;
// use App\Models\Employee;

// class EmployeeLoginController extends Controller
// {
//     public function showLoginForm()
//     {
//         return Inertia::render('Auth/EmployeeLogin');
//     }

//     public function login(Request $request)
//     {
//         $request->validate([
//             'login'    => 'required|string',
//             'password' => 'required|string',
//         ]);

//         \Log::info('Employee login attempt', ['login' => $request->login]);

//         $credentials = [
//             'login'    => $request->login,
//             'password' => $request->password,
//         ];

//         // Проверим, существует ли пользователь с таким логином
//         $user = Employee::where('login', $request->login)->first();
//         if (!$user) {
//             \Log::error('Employee not found', ['login' => $request->login]);
//             return back()->withErrors([
//                 'login' => 'Неверный логин или пароль.',
//             ])->onlyInput('login');
//         }

//         \Log::info('Employee found', ['id' => $user->id, 'role' => $user->role]);

//         // Проверим пароль вручную для отладки
//         if (Hash::check($request->password, $user->passwd)) {
//             \Log::info('Password check passed');
//         } else {
//             \Log::error('Password check failed');
//             // Можно также посмотреть хеш из БД
//             \Log::debug('Stored hash: ' . $user->passwd);
//         }

//         if (Auth::guard('employee')->attempt($credentials, $request->filled('remember'))) {
//             $request->session()->regenerate();
//             \Log::info('Employee login successful', ['id' => $user->id]);

//             $redirectTo = match ($user->role) {
//                 'admin'      => route('dashboard.admin'),
//                 'doctor'     => route('dashboard.doctor'),
//                 'director'   => route('dashboard.director'),
//                 'accountant' => route('dashboard.accountant'),
//                 default      => route('dashboard.admin'),
//             };

//             return redirect()->intended($redirectTo);
//         }

//         \Log::error('Employee authentication failed', ['login' => $request->login]);
//         return back()->withErrors([
//             'login' => 'Неверный логин или пароль.',
//         ])->onlyInput('login');
//     }

//     public function logout(Request $request)
//     {
//         Auth::guard('employee')->logout();
//         $request->session()->invalidate();
//         $request->session()->regenerateToken();

//         return redirect('/');
//     }
// }