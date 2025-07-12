<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WaterReadingController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Modified /dashboard route to redirect based on user role
Route::get('/dashboard', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('user.dashboard');
        }
    }
    return redirect('/login');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User specific routes
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('dashboard', [WaterReadingController::class, 'index'])->name('dashboard');
        Route::get('meter/{meterId}', [WaterReadingController::class, 'show'])->name('meter');
        Route::get('meter/{meterId}/consumption-data', [WaterReadingController::class, 'getConsumptionData'])->name('meter.consumption-data');
        Route::get('water-readings/create', [WaterReadingController::class, 'create'])->name('water-readings.create');
        Route::post('water-readings', [WaterReadingController::class, 'store'])->name('water-readings.store');
        Route::get('meter/{meterId}/export-data', [WaterReadingController::class, 'exportConsumptionData'])->name('meter.export-data');
    });
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('users', [AdminController::class, 'users'])->name('users.index');
    Route::get('users/{user}/manage-meters', [AdminController::class, 'manageMeters'])->name('users.manage-meters');
    Route::post('users/{user}/assign-meter', [AdminController::class, 'assignMeter'])->name('users.assign-meter');
    Route::delete('users/{user}/unassign-meter', [AdminController::class, 'unassignMeter'])->name('users.unassign-meter');
    Route::delete('users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
    // NEW: Route for admin to reset user password
    Route::patch('users/{user}/reset-password', [AdminController::class, 'resetUserPassword'])->name('users.reset-password');

    // Admin Meter Management Routes
    Route::get('meters', [AdminController::class, 'metersIndex'])->name('meters.index');
    Route::get('meters/create', [AdminController::class, 'createMeter'])->name('meters.create');
    Route::post('meters', [AdminController::class, 'storeMeter'])->name('meters.store');
    Route::get('meters/{meter}/edit', [AdminController::class, 'editMeter'])->name('meters.edit');
    Route::patch('meters/{meter}', [AdminController::class, 'updateMeter'])->name('meters.update');
    Route::delete('meters/{meter}', [AdminController::class, 'destroyMeter'])->name('meters.destroy');

    // Admin Reporting Routes
    Route::get('reports/consumption', [AdminController::class, 'consumptionReport'])->name('reports.consumption');
    Route::get('reports/anomaly', [AdminController::class, 'anomalyReport'])->name('reports.anomaly');
});

require __DIR__.'/auth.php';