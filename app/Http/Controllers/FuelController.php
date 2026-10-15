<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FuelController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->companyId($request);
        $records = DB::table('fuel_records')
            ->join('vehicles', 'fuel_records.vehicle_id', '=', 'vehicles.id')
            ->leftJoin('users', 'fuel_records.driver_id', '=', 'users.id')
            ->where('fuel_records.company_id', $companyId)
            ->orderByDesc('fuel_records.refueled_at')->limit(100)
            ->get(['fuel_records.*', 'vehicles.unit_number', 'vehicles.plate', 'users.name as driver_name']);

        return $this->page($request, 'diesel.index', [
            'activePage' => 'diesel',
            'showSidebar' => true,
            'records' => $records,
            'vehicles' => DB::table('vehicles')->where('company_id', $companyId)->orderBy('unit_number')->get(),
            'operators' => DB::table('users')->where('company_id', $companyId)->where('status', 'Activo')->orderBy('name')->get(),
            'stats' => [
                'liters' => $records->sum('liters'),
                'amount' => $records->sum('amount'),
                'events' => $records->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $this->companyId($request);
        $data = $this->validateForm($request, [
            'vehicle_id' => 'required|exists:vehicles,id,company_id,' . $companyId,
            'driver_id' => 'nullable|exists:users,id,company_id,' . $companyId,
            'liters' => 'required|numeric|gt:0|max:100000',
            'amount' => 'required|numeric|gte:0|max:999999999',
            'odometer_km' => 'nullable|integer|min:0',
            'station' => 'nullable|string|max:160',
            'refueled_at' => 'required|date',
            'notes' => 'nullable|string|max:2000',
        ]);
        if (!$data) {
            return $this->invalid($request, '/diesel');
        }

        DB::table('fuel_records')->insert(array_merge($data, [
            'company_id' => $companyId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]));

        $request->session()->flash('success', 'La carga de combustible se guardó.');
        return redirect('/diesel');
    }
}
