<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserInterfacesController extends Controller
{
    public function create(Request $request)
    {
        return $this->page($request, 'usuarios.crear', [
            'showSidebar' => true,
            'activePage' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateForm($request, [
            'name' => 'required|string|max:160',
            'email' => 'required|email|max:160|unique:users,email',
            'phone' => 'nullable|string|max:40',
            'role' => 'required|in:Administrador,Operador,Supervisora,Analista',
            'base' => 'nullable|string|max:160',
            'password' => 'required|string|min:8|confirmed',
        ]);
        if (!$data) {
            return $this->invalid($request, '/usuarios/crear');
        }

        User::create([
            'company_id' => $this->companyId($request),
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'base' => $data['base'] ?? null,
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'status' => 'Activo',
            'preferences' => ['telemetry' => true, 'route_delays' => true, 'expiry_alerts' => true],
        ]);

        $request->session()->flash('success', 'El usuario se guardó correctamente.');
        return redirect('/usuarios');
    }

    public function index(Request $request)
    {
        $companyId = $this->companyId($request);
        $usuarios = User::where('company_id', $companyId)->orderBy('name')->get();

        return $this->page($request, 'usuarios.index', [
            'showSidebar' => true,
            'activePage' => 'users',
            'usuarios' => $usuarios,
            'stats' => [
                'total' => $usuarios->count(),
                'activos' => $usuarios->where('status', 'Activo')->count(),
                'pendientes' => $usuarios->where('status', 'Pendiente')->count(),
                'inactivos' => $usuarios->where('status', 'Inactivo')->count(),
            ],
        ]);
    }

    public function operators(Request $request)
    {
        $usuarios = User::where('company_id', $this->companyId($request))
            ->where('role', '!=', 'Administrador')
            ->orderBy('name')->get();

        return $this->page($request, 'operadores.index', [
            'showSidebar' => true,
            'activePage' => 'operators',
            'usuarios' => $usuarios,
            'tripCounts' => DB::table('trips')->select('driver_id', DB::raw('count(*) as total'))
                ->where('company_id', $this->companyId($request))->groupBy('driver_id')->pluck('total', 'driver_id'),
        ]);
    }

    public function setStatus(Request $request, int $id)
    {
        $user = User::where('company_id', $this->companyId($request))->findOrFail($id);
        if ($user->id === $this->currentUser($request)->id) {
            $request->session()->flash('form_errors', ['No puedes desactivar tu propia cuenta.']);
            return redirect('/usuarios');
        }

        $data = $this->validateForm($request, ['status' => 'required|in:Activo,Inactivo']);
        if (!$data) {
            return $this->invalid($request, '/usuarios');
        }

        $user->status = $data['status'];
        $user->save();
        $request->session()->flash('success', 'Se actualizó el estado del usuario.');
        return redirect('/usuarios');
    }

    public function edit(Request $request)
    {
        $user = $this->currentUser($request);
        return $this->page($request, 'perfil.editar', [
            'showSidebar' => true,
            'activePage' => 'profile',
            'usuario' => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $this->currentUser($request);
        $data = $this->validateForm($request, [
            'name' => 'required|string|max:160',
            'email' => 'required|email|max:160|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:40',
            'base' => 'nullable|string|max:160',
            'telemetry' => 'nullable|boolean',
            'route_delays' => 'nullable|boolean',
            'expiry_alerts' => 'nullable|boolean',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);
        if (!$data) {
            return $this->invalid($request, '/perfil/editar');
        }

        if (!empty($data['new_password']) && !password_verify($data['current_password'] ?? '', $user->password)) {
            $request->session()->flash('form_errors', ['La contraseña actual no coincide.']);
            return redirect('/perfil/editar');
        }

        $user->name = $data['name'];
        $user->email = strtolower($data['email']);
        $user->phone = $data['phone'] ?? null;
        $user->base = $data['base'] ?? null;
        $user->preferences = [
            'telemetry' => !empty($data['telemetry']),
            'route_delays' => !empty($data['route_delays']),
            'expiry_alerts' => !empty($data['expiry_alerts']),
        ];
        if (!empty($data['new_password'])) {
            $user->password = password_hash($data['new_password'], PASSWORD_DEFAULT);
        }
        $user->save();

        $request->session()->flash('success', 'Tu perfil se guardó correctamente.');
        return redirect('/perfil/editar');
    }
}
