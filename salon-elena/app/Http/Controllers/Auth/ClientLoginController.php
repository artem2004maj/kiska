<?php

// namespace App\Http\Controllers\Auth;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Inertia\Inertia;

// class ClientLoginController extends Controller
// {
//     public function create()
//     {
//         return Inertia::render('Auth/Login', [
//             'canResetPassword' => false,
//             'status'           => session('status'),
//         ]);
//     }

//     public function store(Request $request)
//     {
//         $credentials = $request->validate([
//             'email'    => ['required', 'email'],
//             'password' => ['required'],
//         ]);

//         if (! Auth::guard('client')->attempt($credentials, $request->boolean('remember'))) {
//             return back()->withErrors([
//                 'email' => 'Неверный email или пароль.',
//             ])->onlyInput('email');
//         }

//         $user = Auth::guard('client')->user();

//         // Явно логиним пользователя в guard 'client' и регенерируем сессию
//         Auth::guard('client')->login($user, $request->boolean('remember'));

//         $request->session()->regenerate();

//         return redirect()->intended(route('dashboard.client'));
//     }
// }