<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WaterReading;
use App\Models\Meter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash; // NEW: Import Hash facade

class AdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalMeters = Meter::count();

        $hasSystemLeak = false;
        $leakingMeters = [];

        $allUniqueMeters = Meter::pluck('meter_id');

        foreach ($allUniqueMeters as $meterId) {
            $currentDaily = WaterReading::where('meter_id', $meterId)
                                        ->whereDate('reading_at', today())
                                        ->sum('consumption_liters');

            $previousDaily = WaterReading::where('meter_id', $meterId)
                                         ->whereDate('reading_at', today()->subDay())
                                         ->sum('consumption_liters');

            if ($currentDaily > 500 && $previousDaily < 100) {
                $hasSystemLeak = true;
                $leakingMeters[] = $meterId;
            }
        }

        return view('admin.dashboard', compact('totalUsers', 'totalMeters', 'hasSystemLeak', 'leakingMeters'));
    }

    public function users()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function manageMeters(User $user)
    {
        $allMeters = Meter::pluck('meter_id');
        $assignedMeters = WaterReading::where('user_id', $user->id)
                                    ->select('meter_id')
                                    ->distinct()
                                    ->pluck('meter_id');

        return view('admin.users.manage-meters', compact('user', 'allMeters', 'assignedMeters'));
    }

    public function assignMeter(Request $request, User $user)
    {
        $request->validate([
            'meter_id' => 'required|string|max:255|exists:meters,meter_id',
        ]);

        $exists = WaterReading::where('user_id', $user->id)
                            ->where('meter_id', $request->meter_id)
                            ->exists();

        if (!$exists) {
            WaterReading::create([
                'user_id' => $user->id,
                'meter_id' => $request->meter_id,
                'consumption_liters' => 0,
                'reading_at' => now(),
            ]);
            return redirect()->back()->with('success', "Meter {$request->meter_id} assigned to {$user->name}.");
        }

        return redirect()->back()->with('info', "Meter {$request->meter_id} is already assigned to {$user->name}.");
    }

    public function unassignMeter(Request $request, User $user)
    {
        $request->validate([
            'meter_id' => 'required|string|max:255',
        ]);

        $deletedCount = WaterReading::where('user_id', $user->id)
                                    ->where('meter_id', $request->meter_id)
                                    ->delete();

        if ($deletedCount > 0) {
            return redirect()->back()->with('success', "Meter {$request->meter_id} unassigned from {$user->name}.");
        }

        return redirect()->back()->with('info', "Meter {$request->meter_id} was not assigned to {$user->name}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own admin account.');
        }

        WaterReading::where('user_id', $user->id)->delete();
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User and all associated data deleted successfully.');
    }

    public function metersIndex()
    {
        $meters = Meter::all();
        return view('admin.meters.index', compact('meters'));
    }

    public function createMeter()
    {
        return view('admin.meters.create');
    }

    public function storeMeter(Request $request)
    {
        $request->validate([
            'meter_id' => ['required', 'string', 'max:255', Rule::unique('meters', 'meter_id')],
            'location' => ['nullable', 'string', 'max:255'],
            'installation_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        Meter::create($request->all());

        return redirect()->route('admin.meters.index')->with('success', 'Meter created successfully!');
    }

    public function editMeter(Meter $meter)
    {
        return view('admin.meters.edit', compact('meter'));
    }

    public function updateMeter(Request $request, Meter $meter)
    {
        $request->validate([
            'location' => ['nullable', 'string', 'max:255'],
            'installation_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $meter->update($request->only(['location', 'installation_date', 'notes']));

        return redirect()->route('admin.meters.index')->with('success', 'Meter updated successfully!');
    }

    public function destroyMeter(Meter $meter)
    {
        WaterReading::where('meter_id', $meter->meter_id)->delete();
        $meter->delete();

        return redirect()->route('admin.meters.index')->with('success', 'Meter and all associated readings deleted successfully!');
    }

    public function consumptionReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $dailyConsumptionData = WaterReading::selectRaw('DATE(reading_at) as date, SUM(consumption_liters) as total_consumption')
            ->whereBetween('reading_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $labels = $dailyConsumptionData->pluck('date')->toArray();
        $data = $dailyConsumptionData->pluck('total_consumption')->toArray();

        $meterConsumptionData = WaterReading::selectRaw('meter_id, SUM(consumption_liters) as total_consumption')
            ->whereBetween('reading_at', [$startDate, $endDate])
            ->groupBy('meter_id')
            ->orderBy('total_consumption', 'desc')
            ->get();

        return view('admin.reports.consumption', compact('labels', 'data', 'meterConsumptionData', 'startDate', 'endDate'));
    }

    public function anomalyReport(Request $request)
    {
        $anomalies = [];
        $meters = Meter::all();

        foreach ($meters as $meter) {
            $currentDaily = WaterReading::where('meter_id', $meter->meter_id)
                                        ->whereDate('reading_at', today())
                                        ->sum('consumption_liters');

            $previousDaily = WaterReading::where('meter_id', $meter->meter_id)
                                         ->whereDate('reading_at', today()->subDay())
                                         ->sum('consumption_liters');

            if ($currentDaily > 500 && $previousDaily < 100) {
                $anomalies[] = [
                    'meter_id' => $meter->meter_id,
                    'type' => 'Potential Leak',
                    'description' => "Unusually high consumption today ({$currentDaily} L) compared to yesterday ({$previousDaily} L).",
                    'user_email' => $meter->user->email ?? 'N/A',
                    'current_consumption' => $currentDaily,
                    'previous_consumption' => $previousDaily,
                ];
            }

            $highConsumptionThreshold = 1000;

            if ($currentDaily > $highConsumptionThreshold) {
                $isAlreadyLeak = false;
                foreach ($anomalies as $anomaly) {
                    if ($anomaly['meter_id'] === $meter->meter_id && $anomaly['type'] === 'Potential Leak') {
                        $isAlreadyLeak = true;
                        break;
                    }
                }

                if (!$isAlreadyLeak) {
                    $anomalies[] = [
                        'meter_id' => $meter->meter_id,
                        'type' => 'High Consumption',
                        'description' => "Meter consumed {$currentDaily} L today, exceeding system-wide high consumption threshold ({$highConsumptionThreshold} L).",
                        'user_email' => $meter->user->email ?? 'N/A',
                        'current_consumption' => $currentDaily,
                        'previous_consumption' => $previousDaily,
                    ];
                }
            }
        }

        return view('admin.reports.anomaly', compact('anomalies'));
    }

    // NEW: Method to reset a user's password
    public function resetUserPassword(User $user)
    {
        // For simplicity, reset to a default password like 'password'
        // In a real application, you'd generate a random password and email it to the user
        $user->password = Hash::make('password');
        $user->save();

        return redirect()->back()->with('success', "Password for user '{$user->name}' has been reset to 'password'. Please inform the user.");
    }
}