<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BroadcastSmsController;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\SystemController;

/*
|--------------------------------------------------------------------------
| Public / Landing
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Admin (Protected by Auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('doctors', DoctorController::class);
    Route::resource('appointments', AppointmentController::class);
});

/*
|--------------------------------------------------------------------------
| Compatibility alias: route('dashboard')
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| SMS + User Menu (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/sms/broadcast', [BroadcastSmsController::class, 'create'])->name('sms.broadcast.create');
    Route::post('/sms/broadcast', [BroadcastSmsController::class, 'send'])->name('sms.broadcast.send');

    Route::get('/usermenu/assign',         [AccountController::class, 'assign'])->name('usermenu.assign');
    Route::get('/usermenu/show',           [AccountController::class, 'show'])->name('usermenu.show');
    Route::post('/usermenu/toggle-parent', [AccountController::class, 'toggleParent'])->name('usermenu.toggleParent');
    Route::post('/usermenu/toggle-sub',    [AccountController::class, 'toggleSub'])->name('usermenu.toggleSub');
    Route::get('/usermenu/options',        [AccountController::class, 'options'])->name('usermenu.options');
    Route::post('/usermenu/save',          [AccountController::class, 'save'])->name('usermenu.save');
});

Route::match(['get', 'post'], '/system/clear', [SystemController::class, 'clearAllCaches'])
    ->middleware('auth')
    ->name('system.clear');

Route::get('/sms/test', [BroadcastSmsController::class, 'testsend'])->name('sms.testsend');

Route::get('/test-payments', function () {
    try {
        $tableExists = \Illuminate\Support\Facades\Schema::hasTable('payments');
        if ($tableExists) {
            $count = \Illuminate\Support\Facades\DB::table('payments')->count();
            return "Payments table exists with {$count} records";
        } else {
            return "Payments table does not exist";
        }
    } catch (\Exception $e) {
        return "Database error: " . $e->getMessage();
    }
});

/*
|--------------------------------------------------------------------------
| Template Discharge Routes (must be before any wildcard)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::post('/templates/discharge/ajax/add', [TemplateController::class, 'ajaxAddDischarge'])
        ->name('templates.discharge.ajax.add');
    Route::get('/templates/discharge/ajax/get', [TemplateController::class, 'ajaxGetDischarge'])
        ->name('templates.discharge.ajax.get');
});

/*
|--------------------------------------------------------------------------
| Auto-load other route files (auth.php, patient.php, etc.)
| ⚠ Make sure NO other file in routes/ contains toggleSub pointing
|   to UserCreateController — check with: grep -r "toggleSub" routes/
|--------------------------------------------------------------------------
*/
foreach (glob(__DIR__ . '/*.php') as $file) {
    if (basename($file) !== 'web.php') {
        require $file;
    }
}

/*
|--------------------------------------------------------------------------
| Fallback (keep last)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    abort(404);
});