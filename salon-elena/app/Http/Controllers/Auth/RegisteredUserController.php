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
            'phone'    => [
                'nullable',
                'string',
                'max:20',
                'regex:/^(\+7|8)[0-9]{10}$/',
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'phone.regex' => 'Номер телефона должен быть в формате +7XXXXXXXXXX или 8XXXXXXXXXX (10 цифр после кода)',
        ]);

        $client = Client::create([
            'client_name' => $request->name,
            'email'       => $request->email,
            'login'       => $request->login,
            'passwd'      => Hash::make($request->password),
            'phone'       => $request->phone ?? null,
            'birth_date'  => now()->subYears(25),
        ]);

        event(new Registered($client));

        Auth::guard('client')->login($client);

        return redirect()->route('dashboard.client');
    }
}