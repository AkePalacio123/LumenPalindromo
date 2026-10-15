<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FleetController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->companyId($request);
        $vehicles = DB::table('vehicles')->where('company_id', $companyId)->orderBy('unit_number')->get();

        return $this->page($request, 'flotilla.index', [
            'activePage' => 'fleet',
            'showSidebar' => true,
            'vehicles' => $vehicles,
            'stats' => [
                'total' => $vehicles->count(),
                'available' => $vehicles->where('status', 'Disponible')->count(),
                'onRoute' => $vehicles->where('status', 'En ruta')->count(),
                'maintenance' => $vehicles->where('status', 'Mantenimiento')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateForm($request, [
            'unit_number' => 'required|string|max:32|unique:vehicles,unit_number',
            'plate' => 'required|string|max:32|unique:vehicles,plate',
            'make' => 'nullable|string|max:80',
            'model' => 'nullable|string|max:80',
            'year' => 'nullable|integer|min:1970|max:' . (date('Y') + 2),
            'capacity_tons' => 'nullable|numeric|min:0|max:999999',
            'status' => 'required|in:Disponible,En ruta,Mantenimiento,Fuera de servicio',
            'last_location' => 'nullable|string|max:160',
        ]);
        if (!$data) {
            return $this->invalid($request, '/flotilla');
        }

        $data['company_id'] = $this->companyId($request);
        $data['unit_number'] = strtoupper($data['unit_number']);
        $data['plate'] = strtoupper($data['plate']);
        DB::table('vehicles')->insert(array_merge($data, ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]));

        $request->session()->flash('success', 'La unidad se agregó a la flotilla.');
        return redirect('/flotilla');
    }

    public function updateStatus(Request $request, int $id)
    {
        $data = $this->validateForm($request, ['status' => 'required|in:Disponible,En ruta,Mantenimiento,Fuera de servicio']);
        if (!$data) {
            return $this->invalid($request, '/flotilla');
        }

        $updated = DB::table('vehicles')->where('company_id', $this->companyId($request))->where('id', $id)
            ->update(['status' => $data['status'], 'updated_at' => Carbon::now()]);
        if (!$updated) {
            abort(404);
        }

        $request->session()->flash('success', 'El estado de la unidad se actualizó.');
        return redirect('/flotilla');
    }

    public function destroy(Request $request, int $id)
    {
        $query = DB::table('vehicles')->where('company_id', $this->companyId($request))->where('id', $id);
        if (!$query->exists()) {
            abort(404);
        }
        if (DB::table('trips')->where('vehicle_id', $id)->exists() || DB::table('fuel_records')->where('vehicle_id', $id)->exists()) {
            $request->session()->flash('form_errors', ['La unidad tiene viajes o cargas de combustible asociados y no se puede eliminar.']);
            return redirect('/flotilla');
        }

        $query->delete();
        $request->session()->flash('success', 'La unidad se eliminó de la flotilla.');
        return redirect('/flotilla');
    }
}
