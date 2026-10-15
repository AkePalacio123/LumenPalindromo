<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->companyId($request);
        $monthStart = Carbon::now()->startOfMonth()->toDateTimeString();

        $stats = [
            'vehicles' => DB::table('vehicles')->where('company_id', $companyId)->count(),
            'availableVehicles' => DB::table('vehicles')->where('company_id', $companyId)->where('status', 'Disponible')->count(),
            'activeTrips' => DB::table('trips')->where('company_id', $companyId)->whereIn('status', ['En ruta', 'Programado'])->count(),
            'operators' => DB::table('users')->where('company_id', $companyId)->where('role', '!=', 'Administrador')->count(),
            'fuelSpent' => DB::table('fuel_records')->where('company_id', $companyId)->where('refueled_at', '>=', $monthStart)->sum('amount'),
        ];

        $trips = DB::table('trips')
            ->leftJoin('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
            ->leftJoin('users', 'trips.driver_id', '=', 'users.id')
            ->where('trips.company_id', $companyId)
            ->orderByDesc('trips.updated_at')
            ->limit(6)
            ->get(['trips.*', 'vehicles.unit_number', 'users.name as driver_name']);

        return $this->page($request, 'dashboard-demo', compact('stats', 'trips'));
    }
}
