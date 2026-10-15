<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function loginForm(Request $request)
    {
        if ($request->session()->has('user_id')) {
            return redirect('/dashboard');
        }

        return $this->page($request, 'auth.login');
    }

    public function login(Request $request)
    {
        $data = $this->validateForm($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        if (!$data) {
            return $this->invalid($request, '/login');
        }

        $user = User::where('email', strtolower($data['email']))->first();
        if (!$user || $user->status !== 'Activo' || !password_verify($data['password'], $user->password)) {
            $request->session()->flash('form_errors', ['Correo o contraseña incorrectos.']);
            return redirect('/login');
        }

        $request->session()->regenerate();
        $request->session()->put('user_id', $user->id);
        $request->session()->flash('success', 'Bienvenido a FleetLogix.');

        return redirect('/dashboard');
    }

    public function registerForm(Request $request)
    {
        return $this->page($request, 'auth.register');
    }

    public function register(Request $request)
    {
        $data = $this->validateForm($request, [
            'company_name' => 'required|string|max:160',
            'tax_id' => 'nullable|string|max:32',
            'name' => 'required|string|max:160',
            'email' => 'required|email|max:160|unique:users,email',
            'phone' => 'nullable|string|max:40',
            'address' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'accepted',
        ]);
        if (!$data) {
            return $this->invalid($request, '/registro');
        }

        $user = DB::transaction(function () use ($data) {
            $company = Company::create([
                'name' => $data['company_name'],
                'tax_id' => $data['tax_id'] ?? null,
                'email' => strtolower($data['email']),
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => 'active',
            ]);

            return User::create([
                'company_id' => $company->id,
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'phone' => $data['phone'] ?? null,
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => 'Administrador',
                'status' => 'Activo',
                'preferences' => ['telemetry' => true, 'route_delays' => true, 'expiry_alerts' => true],
            ]);
        });

        $request->session()->regenerate();
        $request->session()->put('user_id', $user->id);
        $request->session()->flash('success', 'Empresa registrada. Ya puedes usar FleetLogix.');

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
