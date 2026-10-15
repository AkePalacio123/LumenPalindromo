<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TravelController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->companyId($request);
        $trips = DB::table('trips')
            ->leftJoin('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->leftJoin('users', 'trips.driver_id', '=', 'users.id')
            ->where('trips.company_id', $companyId)
            ->orderByDesc('trips.departure_at')->get(['trips.*', 'vehicles.unit_number', 'users.name as driver_name']);

        return $this->page($request, 'viajes.index', [
            'activePage' => 'trips',
            'showSidebar' => true,
            'trips' => $trips,
            'stats' => [
                'total' => $trips->count(),
                'inRoute' => $trips->where('status', 'En ruta')->count(),
                'scheduled' => $trips->where('status', 'Programado')->count(),
                'completed' => $trips->where('status', 'Completado')->count(),
            ],
        ]);
    }

    public function create(Request $request)
    {
        return $this->page($request, 'viajes.crear', array_merge($this->formOptions($request), [
            'activePage' => 'trips',
            'showSidebar' => true,
        ]));
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId($request);
        $data = $this->validateForm($request, [
            'trip_number' => 'nullable|string|max:40|unique:trips,trip_number',
            'origin' => 'required|string|max:160',
            'destination' => 'required|string|max:160',
            'cargo' => 'nullable|string|max:160',
            'departure_at' => 'nullable|date',
            'eta' => 'nullable|date|after_or_equal:departure_at',
            'vehicle_id' => 'nullable|exists:vehicles,id,company_id,' . $companyId,
            'driver_id' => 'nullable|exists:users,id,company_id,' . $companyId,
            'distance_km' => 'nullable|numeric|min:0|max:9999999',
            'notes' => 'nullable|string|max:2000',
        ]);
        if (!$data) {
            return $this->invalid($request, '/viajes/crear');
        }

        DB::table('trips')->insert([
            'company_id' => $companyId,
            'trip_number' => $data['trip_number'] ?: 'VJ-' . Carbon::now()->format('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
            'origin' => $data['origin'],
            'destination' => $data['destination'],
            'cargo' => $data['cargo'] ?? null,
            'departure_at' => $data['departure_at'] ?? null,
            'eta' => $data['eta'] ?? null,
            'status' => 'Programado',
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'distance_km' => $data['distance_km'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $request->session()->flash('success', 'El viaje quedó programado.');
        return redirect('/viajes');
    }

    public function updateStatus(Request $request, int $id)
    {
        $data = $this->validateForm($request, ['status' => 'required|in:Programado,En ruta,Completado,Cancelado']);
        if (!$data) {
            return $this->invalid($request, '/viajes');
        }
        $updated = DB::table('trips')->where('company_id', $this->companyId($request))->where('id', $id)
            ->update(['status' => $data['status'], 'updated_at' => Carbon::now()]);
        if (!$updated) {
            abort(404);
        }

        $request->session()->flash('success', 'Se actualizó el estado del viaje.');
        return redirect('/viajes');
    }

    public function destroy(Request $request, int $id)
    {
        $deleted = DB::table('trips')->where('company_id', $this->companyId($request))->where('id', $id)->delete();
        if (!$deleted) {
            abort(404);
        }
        $request->session()->flash('success', 'El viaje se eliminó.');
        return redirect('/viajes');
    }

    private function formOptions(Request $request): array
    {
        $companyId = $this->companyId($request);
        return [
            'vehicles' => DB::table('vehicles')->where('company_id', $companyId)->orderBy('unit_number')->get(),
            'operators' => DB::table('users')->where('company_id', $companyId)->where('status', 'Activo')->orderBy('name')->get(),
        ];
    }
}
