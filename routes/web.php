<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NpcEventController;
use App\Http\Controllers\NpcPartController;
use App\Http\Controllers\NpcProcessController;
use App\Http\Controllers\NpcDeliveryTargetController;
use App\Http\Controllers\NpcMasterCheckpointController;
use App\Http\Controllers\NpcMasterDepartmentController;
use App\Http\Controllers\NpcMasterRoutingController;
use App\Http\Controllers\ProductChecksheetSetupController;
use App\Http\Controllers\NpcInternalCategoryController;
use App\Http\Controllers\NpcCustomerCategoryController;
use App\Http\Controllers\NpcDeliveryGroupController;
use App\Http\Controllers\NpcMasterEventController;
use App\Http\Controllers\NpcPartProcessController;
use App\Http\Controllers\ProductionTrackingController;
use App\Http\Controllers\NpcChecksheetController;

Route::get('/check-session-status', function () {
    return response()->json(['active' => Auth::check()]);
})->name('session.check');
// Route for redirecting to Central SSO Portal
Route::get('/login', function () {
    return redirect(env('PORTAL_LOGIN_URL', 'https://promise.summitadyawinsa.co.id/login'));
})->name('login');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    return redirect(env('PORTAL_LOGIN_URL', 'https://promise.summitadyawinsa.co.id/login'));
})->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dummy Profile Route
    Route::get('/profile', function () {
        return 'Profile Page';
    })->name('profile.index');

    // NPC Events Master Route
    Route::get('/events/import', [NpcEventController::class, 'importForm'])->name('events.import');
    Route::post('/events/import', [NpcEventController::class, 'importData'])->name('events.import.store');
    Route::resource('events', NpcEventController::class);
    Route::resource('events.parts', NpcPartController::class);

    // Master Data Routes
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('processes', NpcProcessController::class)->except(['show']);
        Route::resource('delivery-targets', NpcDeliveryTargetController::class)->except(['show']);
        Route::resource('checkpoints', NpcMasterCheckpointController::class)->except(['show']);
        Route::resource('departments', NpcMasterDepartmentController::class)->except(['show']);
        // Menambahkan Routings Route tapi dengan parameter part_id khusus
        Route::resource('routings', NpcMasterRoutingController::class)->except(['show']);

        // Master Checksheet Mapping based on Product
        Route::get('product-checksheets', [ProductChecksheetSetupController::class, 'index'])->name('checksheets.index');

        Route::resource('internal-categories', NpcInternalCategoryController::class)->except(['show']);
        Route::resource('customer-categories', NpcCustomerCategoryController::class)->except(['show']);
        Route::resource('delivery-groups', NpcDeliveryGroupController::class)->except(['show']);
        Route::resource('events', NpcMasterEventController::class)->except(['show']);
    });

    // Dummy API Routes for Dashboard Filters
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/data/models', function (\Illuminate\Http\Request $request) {
            $models = \App\Models\VehicleModel::where('customer_id', $request->customer_id)->get(['id', 'name as text']);
            return response()->json(['results' => $models]);
        })->name('data.models');

        Route::post('/data/master-events', function (\Illuminate\Http\Request $request) {
            $events = \App\Models\NpcMasterEvent::where('customer_id', $request->customer_id)
                ->where('model_id', $request->model_id)
                ->orderBy('name')
                ->get(['id', 'name as text']);
            return response()->json(['results' => $events]);
        })->name('data.master-events');

        Route::post('/data/products', function (\Illuminate\Http\Request $request) {
            $query = \App\Models\Product::with('vehicleModel.customer');

            // Filter by model_id if provided
            if ($request->filled('model_id')) {
                $query->where('model_id', $request->model_id);
            }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('part_no', 'like', '%' . $request->search . '%')
                        ->orWhere('part_name', 'like', '%' . $request->search . '%');
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
                $prod->customer_name = ($prod->vehicleModel && $prod->vehicleModel->customer) ? $prod->vehicleModel->customer->name : 'N/A';
            }

            return response()->json(['results' => $products]);
        })->name('data.products');

        Route::post('/data/customers', function () {
            return response()->json(['results' => []]);
        })->name('data.customers');
        Route::get('/data/statuses', function () {
            return response()->json(['results' => []]);
        })->name('data.statuses');

        Route::post('/data/customer-categories', function (\Illuminate\Http\Request $request) {
            $categories = \App\Models\NpcCustomerCategory::where('customer_id', $request->customer_id)->get(['id', 'name as text']);
            return response()->json(['results' => $categories]);
        })->name('data.customer-categories');
    });

    // Part Routing Routes
    Route::get('/parts/{part}/routing', [NpcPartProcessController::class, 'edit'])->name('parts.routing.edit');
    Route::post('/parts/{part}/routing', [NpcPartProcessController::class, 'update'])->name('parts.routing.update');

    // Production Tracking Route
    Route::get('/tracking', [ProductionTrackingController::class, 'index'])->name('tracking.index');
    Route::get('/tracking/setup', [ProductionTrackingController::class, 'setup'])->name('tracking.setup');
    Route::get('/tracking/production', [ProductionTrackingController::class, 'production'])->name('tracking.production');
    Route::get('/tracking/qc', [ProductionTrackingController::class, 'qc'])->name('tracking.qc');
    Route::get('/tracking/mgm', [ProductionTrackingController::class, 'mgm'])->name('tracking.mgm');
    Route::get('/tracking/stock', [ProductionTrackingController::class, 'stock'])->name('tracking.stock');
    Route::get('/tracking/history', [ProductionTrackingController::class, 'history'])->name('tracking.history');

    // Status update and action routes
    Route::post('/tracking/{part}/status', [ProductionTrackingController::class, 'updateStatus'])->name('tracking.status.update');
    Route::post('/tracking/{part}/process-complete', [ProductionTrackingController::class, 'completeProcess'])->name('tracking.process.complete');
    Route::post('/tracking/{part}/deliver', [ProductionTrackingController::class, 'deliver'])->name('tracking.deliver');

    // Quality Checksheet Routes
    Route::get('/tracking/products/{product}/checksheet-setup', [ProductChecksheetSetupController::class, 'edit'])->name('checksheets.setup.edit');
    Route::post('/tracking/products/{product}/checksheet-setup', [ProductChecksheetSetupController::class, 'update'])->name('checksheets.setup.update');
    Route::get('/tracking/{part}/checksheet/create', [NpcChecksheetController::class, 'create'])->name('checksheets.create');
    Route::get('/checksheets/{checksheet}/edit', [NpcChecksheetController::class, 'edit'])->name('checksheets.edit');
    Route::put('/checksheets/{checksheet}', [NpcChecksheetController::class, 'update'])->name('checksheets.update');
});
