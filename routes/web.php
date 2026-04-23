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
        // Menambahkan Routings Route tapi dengan parameter part_id khusus
        Route::post('routings/reorder', [\App\Http\Controllers\NpcMasterRoutingController::class, 'reorder'])->name('routings.reorder');
        Route::resource('routings', \App\Http\Controllers\NpcMasterRoutingController::class)->except(['show']);
        
        // Master Checksheet Mapping based on Product
        Route::get('product-checksheets', [\App\Http\Controllers\ProductChecksheetSetupController::class, 'index'])->name('checksheets.index');

        Route::resource('internal-categories', \App\Http\Controllers\NpcInternalCategoryController::class)->except(['show']);
        Route::resource('customer-categories', \App\Http\Controllers\NpcCustomerCategoryController::class)->except(['show']);
        Route::resource('delivery-groups', \App\Http\Controllers\NpcDeliveryGroupController::class)->except(['show']);
    });

    // Dummy API Routes for Dashboard Filters
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/data/models', function (\Illuminate\Http\Request $request) { 
            $models = \App\Models\VehicleModel::where('customer_id', $request->customer_id)->get(['id', 'name as text']);
            return response()->json(['results' => $models]); 
        })->name('data.models');


        Route::post('/data/products', function (\Illuminate\Http\Request $request) {
            $query = \App\Models\Product::with('vehicleModel.customer');
            
            // HANYA tampilkan product yang sudah disetup routing (proses) ATAU checksheet-nya
            $query->where(function($q) {
                $q->whereIn('id', function($sub) {
                    $sub->select('part_id')->from('npc_master_routings');
                })->orWhereHas('mappedCheckpoints');
            });
            
            // Filter by model_id if provided
            if ($request->filled('model_id')) {
                $query->where('model_id', $request->model_id);
            }

            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('part_no', 'like', '%'.$request->search.'%')
                      ->orWhere('part_name', 'like', '%'.$request->search.'%');
                });
            }
            // Tambahkan order by untuk relevansi
            $query->orderBy('part_no', 'asc');
            
            $products = $query->limit(30)->get();
            
            // Include default process_name from NpcMasterRouting mapping
            foreach ($products as $prod) {
                $routing = \App\Models\NpcMasterRouting::with('process')
                            ->where('part_id', $prod->id)
                            ->orderBy('sequence_order', 'asc')
                            ->first();
                            
                $prod->process_name = ($routing && $routing->process) ? $routing->process->process_name : null;
                $prod->model_name = $prod->vehicleModel ? $prod->vehicleModel->name : 'N/A';
                $prod->customer_name = ($prod->vehicleModel && $prod->vehicleModel->customer) ? $prod->vehicleModel->customer->code : 'N/A';
            }
            
            return response()->json(['results' => $products]);
        })->name('data.products');

        Route::post('/data/customers', function () { return response()->json(['results' => []]); })->name('data.customers');
        Route::get('/data/statuses', function () { return response()->json(['results' => []]); })->name('data.statuses');
        
        Route::post('/data/customer-categories', function (\Illuminate\Http\Request $request) {
            $categories = \App\Models\NpcCustomerCategory::where('customer_id', $request->customer_id)->get(['id', 'name as text']);
            return response()->json(['results' => $categories]);
        })->name('data.customer-categories');
    });

    // Part Routing Routes
    Route::get('/parts/{part}/routing', [\App\Http\Controllers\NpcPartProcessController::class, 'edit'])->name('parts.routing.edit');
    Route::post('/parts/{part}/routing', [\App\Http\Controllers\NpcPartProcessController::class, 'update'])->name('parts.routing.update');

    // Production Tracking Route
    Route::get('/tracking', [\App\Http\Controllers\ProductionTrackingController::class, 'index'])->name('tracking.index');
    Route::get('/tracking/setup', [\App\Http\Controllers\ProductionTrackingController::class, 'setup'])->name('tracking.setup');
    Route::get('/tracking/production', [\App\Http\Controllers\ProductionTrackingController::class, 'production'])->name('tracking.production');
    Route::get('/tracking/qc', [\App\Http\Controllers\ProductionTrackingController::class, 'qc'])->name('tracking.qc');
    Route::get('/tracking/mgm', [\App\Http\Controllers\ProductionTrackingController::class, 'mgm'])->name('tracking.mgm');
    Route::get('/tracking/stock', [\App\Http\Controllers\ProductionTrackingController::class, 'stock'])->name('tracking.stock');
    Route::get('/tracking/history', [\App\Http\Controllers\ProductionTrackingController::class, 'history'])->name('tracking.history');
    
    // Status update and action routes
    Route::post('/tracking/{part}/status', [\App\Http\Controllers\ProductionTrackingController::class, 'updateStatus'])->name('tracking.status.update');
    Route::post('/tracking/{part}/setup-rollback', [\App\Http\Controllers\ProductionTrackingController::class, 'rollbackSetup'])->name('tracking.setup.rollback');
    Route::post('/tracking/{part}/process-complete', [\App\Http\Controllers\ProductionTrackingController::class, 'completeProcess'])->name('tracking.process.complete');
    Route::post('/tracking/{part}/process-rollback', [\App\Http\Controllers\ProductionTrackingController::class, 'rollbackProcess'])->name('tracking.process.rollback');
    Route::post('/tracking/{part}/deliver', [\App\Http\Controllers\ProductionTrackingController::class, 'deliver'])->name('tracking.deliver');
    Route::post('/parts/{part}/apply-ecn', [\App\Http\Controllers\NpcPartController::class, 'applyEcn'])->name('parts.apply-ecn');

    // Quality Checksheet Routes
    Route::get('/tracking/products/{product}/checksheet-setup', [\App\Http\Controllers\ProductChecksheetSetupController::class, 'edit'])->name('checksheets.setup.edit');
    Route::post('/tracking/products/{product}/checksheet-setup', [\App\Http\Controllers\ProductChecksheetSetupController::class, 'update'])->name('checksheets.setup.update');
    Route::get('/tracking/{part}/checksheet/create', [\App\Http\Controllers\NpcChecksheetController::class, 'create'])->name('checksheets.create');
    Route::get('/checksheets/{checksheet}/edit', [\App\Http\Controllers\NpcChecksheetController::class, 'edit'])->name('checksheets.edit');
    Route::put('/checksheets/{checksheet}', [\App\Http\Controllers\NpcChecksheetController::class, 'update'])->name('checksheets.update');
});
