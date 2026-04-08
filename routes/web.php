<?php

use App\Http\Controllers\AuthController;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login_post');
Route::get('/forget-password', [AuthController::class, 'forgetPassword'])->name('forget_password');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Dummy Profile Route
    Route::get('/profile', function () { return 'Profile Page'; })->name('profile.index');

    // NPC Events Master Route
    Route::get('/events/import', [\App\Http\Controllers\NpcEventController::class, 'importForm'])->name('events.import');
    Route::post('/events/import', [\App\Http\Controllers\NpcEventController::class, 'importData'])->name('events.import.store');
    Route::resource('events', \App\Http\Controllers\NpcEventController::class);
    Route::resource('events.parts', \App\Http\Controllers\NpcPartController::class);

    // Master Data Routes
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('processes', \App\Http\Controllers\NpcProcessController::class)->except(['show']);
        Route::resource('delivery-targets', \App\Http\Controllers\NpcDeliveryTargetController::class)->except(['show']);
        Route::resource('checkpoints', \App\Http\Controllers\NpcMasterCheckpointController::class)->except(['show']);
        Route::resource('departments', \App\Http\Controllers\NpcMasterDepartmentController::class)->except(['show']);
    });

    // Dummy API Routes for Dashboard Filters
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/data/models', function (\Illuminate\Http\Request $request) { 
            $models = \App\Models\VehicleModel::where('customer_id', $request->customer_id)->get(['id', 'name as text']);
            return response()->json(['results' => $models]); 
        })->name('data.models');
        
        Route::post('/data/products', function (\Illuminate\Http\Request $request) {
            $query = \App\Models\Product::query();
            
            // Hapus filter model_id agar part dari model manapun bisa dicari
            // (Karena terkadang part order bisa lintas model atau modelnya di set 'All' di Drawing)

            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('part_no', 'like', '%'.$request->search.'%')
                      ->orWhere('part_name', 'like', '%'.$request->search.'%');
                });
            }
            // Tambahkan order by untuk relevansi
            $query->orderBy('part_no', 'asc');
            
            $products = $query->limit(30)->get(['id', 'part_no', 'part_name']);
            return response()->json(['results' => $products]);
        })->name('data.products');

        Route::post('/data/customers', function () { return response()->json(['results' => []]); })->name('data.customers');
        Route::get('/data/statuses', function () { return response()->json(['results' => []]); })->name('data.statuses');
    });

    // Part Routing Routes
    Route::get('/parts/{part}/routing', [\App\Http\Controllers\NpcPartProcessController::class, 'edit'])->name('parts.routing.edit');
    Route::post('/parts/{part}/routing', [\App\Http\Controllers\NpcPartProcessController::class, 'update'])->name('parts.routing.update');

    // Production Tracking Route
    Route::get('/tracking', [\App\Http\Controllers\ProductionTrackingController::class, 'index'])->name('tracking.index');
    Route::post('/tracking/{part}/status', [\App\Http\Controllers\ProductionTrackingController::class, 'updateStatus'])->name('tracking.status.update');
    Route::post('/tracking/{part}/deliver', [\App\Http\Controllers\ProductionTrackingController::class, 'deliver'])->name('tracking.deliver');

    // Quality Checksheet Routes
    Route::get('/tracking/{part}/checksheet/create', [\App\Http\Controllers\NpcChecksheetController::class, 'create'])->name('checksheets.create');
    Route::get('/checksheets/{checksheet}/edit', [\App\Http\Controllers\NpcChecksheetController::class, 'edit'])->name('checksheets.edit');
    Route::put('/checksheets/{checksheet}', [\App\Http\Controllers\NpcChecksheetController::class, 'update'])->name('checksheets.update');
});
