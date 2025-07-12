<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WaterReading;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\CarbonPeriod;
use Carbon\Carbon;
use App\Models\Meter; // Import Meter model if needed for future features
use Symfony\Component\HttpFoundation\StreamedResponse; // NEW: Import for file download

class WaterReadingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        $metersData = WaterReading::where('user_id', $userId)
            ->select('meter_id')
            ->distinct()
            ->get()
            ->map(function ($reading) use ($userId, $user) {
                $meterId = $reading->meter_id;

                $latest = WaterReading::where('meter_id', $meterId)
                    ->where('user_id', $userId)
                    ->latest('reading_at')
                    ->first();

                $currentDaily = WaterReading::where('meter_id', $meterId)
                    ->where('user_id', $userId)
                    ->whereDate('reading_at', today())
                    ->sum('consumption_liters');

                $previousDaily = WaterReading::where('meter_id', $meterId)
                    ->where('user_id', $userId)
                    ->whereDate('reading_at', today()->subDay())
                    ->sum('consumption_liters');

                $hasLeak = ($currentDaily > 500 && $previousDaily < 100);

                $hasHighConsumption = false;
                if ($user->daily_consumption_alert_threshold !== null && $user->daily_consumption_alert_threshold > 0) {
                    $hasHighConsumption = ($currentDaily > $user->daily_consumption_alert_threshold);
                }

                return [
                    'meter_id' => $meterId,
                    'latest_consumption_liters' => $latest ? $latest->consumption_liters : 0,
                    'latest_reading_at' => $latest ? $latest->reading_at->format('Y-m-d H:i:s') : 'N/A',
                    'current_daily_consumption' => $currentDaily,
                    'previous_daily_consumption' => $previousDaily,
                    'has_potential_leak' => $hasLeak,
                    'has_high_consumption' => $hasHighConsumption,
                ];
            });

        return view('user.dashboard', ['metersData' => $metersData]);
    }

    public function show($meterId)
    {
        $userId = Auth::id();

        $meterExistsForUser = WaterReading::where('user_id', $userId)
            ->where('meter_id', $meterId)
            ->exists();

        if (!$meterExistsForUser) {
            return redirect()->route('user.dashboard')->with('error', 'Meter not found or not assigned to your account.');
        }

        return view('meter_detail', compact('meterId'));
    }

    public function getConsumptionData(Request $request, $meterId)
    {
        try {
            $userId = Auth::id();
            $days = (int) $request->input('days', 30);
            $endDate = now();
            $startDate = now()->subDays($days)->startOfDay();

            $readings = WaterReading::where('meter_id', $meterId)
                ->where('user_id', $userId)
                ->whereBetween('reading_at', [$startDate, $endDate])
                ->orderBy('reading_at')
                ->get();

            $dailyConsumption = [];
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                $dailyConsumption[$date->toDateString()] = 0;
            }

            foreach ($readings as $reading) {
                $date = $reading->reading_at->toDateString();
                if (array_key_exists($date, $dailyConsumption)) {
                    $dailyConsumption[$date] += $reading->consumption_liters;
                }
            }

            ksort($dailyConsumption);

            $historicalData = array_values($dailyConsumption);
            $averageDailyConsumption = count($historicalData) > 0
                ? array_sum($historicalData) / count($historicalData)
                : 0;

            $predictionDays = 7;
            $predictedLabels = [];
            $predictedData = [];

            for ($i = 1; $i <= $predictionDays; ++$i) {
                $predictedDate = Carbon::parse($endDate)->addDays($i)->toDateString();
                $predictedLabels[] = $predictedDate;
                $predictedData[] = round($averageDailyConsumption, 2);
            }

            return response()->json([
                'labels' => array_keys($dailyConsumption),
                'data' => array_values($dailyConsumption),
                'predictedLabels' => $predictedLabels,
                'predictedData' => $predictedData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fetching consumption data: ' . $e->getMessage()
            ], 500);
        }
    }

    // NEW: Method to export consumption data as CSV
    public function exportConsumptionData(Request $request, $meterId)
    {
        $userId = Auth::id();

        // Ensure the meter belongs to the user
        $meterExistsForUser = WaterReading::where('user_id', $userId)
            ->where('meter_id', $meterId)
            ->exists();

        if (!$meterExistsForUser) {
            return redirect()->route('user.dashboard')->with('error', 'Meter not found or not assigned to your account.');
        }

        $days = (int) $request->input('days', 30); // Get days from request, default to 30
        $endDate = now();
        $startDate = now()->subDays($days)->startOfDay();

        $readings = WaterReading::where('meter_id', $meterId)
            ->where('user_id', $userId)
            ->whereBetween('reading_at', [$startDate, $endDate])
            ->orderBy('reading_at')
            ->get();

        $dailyConsumption = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dailyConsumption[$date->toDateString()] = 0;
        }

        foreach ($readings as $reading) {
            $date = $reading->reading_at->toDateString();
            if (array_key_exists($date, $dailyConsumption)) {
                $dailyConsumption[$date] += $reading->consumption_liters;
            }
        }

        ksort($dailyConsumption);

        $filename = "meter_{$meterId}_consumption_data_" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($dailyConsumption) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Daily Consumption (Liters)']); // CSV Header

            foreach ($dailyConsumption as $date => $consumption) {
                fputcsv($file, [$date, $consumption]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function create()
    {
        return view('water_readings.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'meter_id' => [
                'required',
                'string',
                'max:255',
                Rule::exists('water_readings', 'meter_id')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ],
            'consumption_liters' => ['required', 'numeric', 'min:0'],
            'reading_at' => ['required', 'date'],
        ]);

        WaterReading::create([
            'user_id' => Auth::id(),
            'meter_id' => $request->meter_id,
            'consumption_liters' => $request->consumption_liters,
            'reading_at' => $request->reading_at,
        ]);

        return redirect()->route('user.dashboard')->with('success', 'Water reading submitted successfully!');
    }
}