<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomerAuthController extends Controller
{
    /**
     * Halaman Login
     */
    public function login()
    {
        return view('customer.auth.login');
    }

    /**
     * Proses Login
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $credentials['is_active'] = true;

        if (Auth::guard('customer')->attempt($credentials)) {

            $request->session()->regenerate();

            return redirect()->intended('/')
                ->with('success', 'Login berhasil.');
        }

        return back()
            ->withErrors([
                'phone' => 'Nomor HP atau password tidak sesuai.',
            ])
            ->onlyInput('phone');
    }

    /**
     * Halaman Register
     */
    public function register()
    {
        return view('customer.auth.register');
    }

    /**
     * Proses Register
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:150', 'unique:customers,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customer = Customer::create([
            'customer_code' => 'CUS-' . Str::upper(Str::random(8)),
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'],
            'is_active' => true,
        ]);

        Auth::guard('customer')->login($customer);

        $request->session()->regenerate();

        return redirect('/')
            ->with('success', 'Registrasi berhasil. Selamat datang!');
    }

    /**
     * Logout Customer
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        // Pertahankan session cart pelanggan.
        $request->session()->regenerate();

        return redirect('/')
            ->with('success', 'Anda berhasil logout.');
    }
}