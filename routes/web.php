<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Jetstream\Jetstream;

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

Route::get('/', [HomeController::class, 'index']);

Route::get('/shared-report', static function () {
    return Inertia::render('SharedReport');
})->name('shared-report');

// Authentication routes - redirect to Filament admin panel
// Filament handles these automatically, but we provide legacy redirects for compatibility
Route::redirect('/login', '/admin/login')->name('login');
Route::redirect('/register', '/admin/register')->name('register');
Route::redirect('/forgot-password', '/admin/forgot-password')->name('password.request');

// Dashboard redirects to Filament admin dashboard
Route::redirect('/dashboard', '/admin', 301)->name('dashboard');

/*Route::middleware([
    'auth:web',
    config('jetstream.auth_session'),
    'verified',
])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    Route::get('/time', static function () {
        return Inertia::render('Time');
    })->name('time');

    Route::get('/calendar', static function () {
        return Inertia::render('Calendar');
    })->name('calendar');

    Route::get('/timesheet', static function () {
        return Inertia::render('Timesheet');
    })->name('timesheet');

    Route::get('/reporting', static function () {
        return Inertia::render('Reporting');
    })->name('reporting');

    Route::get('/reporting/detailed', static function () {
        return Inertia::render('ReportingDetailed');
    })->name('reporting.detailed');

    Route::get('/reporting/shared', static function () {
        return Inertia::render('ReportingShared');
    })->name('reporting.shared');

    Route::get('/projects', static function () {
        return Inertia::render('Projects');
    })->name('projects');

    Route::get('/projects/{project}', static function () {
        return Inertia::render('ProjectShow');
    })->name('projects.show');

    Route::get('/clients', static function () {
        return Inertia::render('Clients');
    })->name('clients');

    Route::get('/members', static function () {
        return Inertia::render('Members', [
            'availableRoles' => array_values(Jetstream::$roles),
        ]);
    })->name('members');

    Route::get('/tags', static function () {
        return Inertia::render('Tags');
    })->name('tags');

    Route::get('/import', static function () {
        return Inertia::render('Import');
    })->name('import');
});*/
