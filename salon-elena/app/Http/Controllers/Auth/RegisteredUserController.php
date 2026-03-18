<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|lowercase|email|max:255|unique:clients,email',
            'login'    => 'required|string|max:56|unique:clients,login',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $client = Client::create([
            'client_name' => $request->name,
            'email'       => $request->email,
            'login'       => $request->login,
            'passwd'      => Hash::make($request->password),
            'phone'       => 0,
            'birth_date'  => now()->subYears(25),
        ]);

        event(new Registered($client));

        Auth::guard('client')->login($client);

        // ИСПРАВЛЕНО: используем правильное имя маршрута
        return redirect()->route('dashboard.client');
    }
}