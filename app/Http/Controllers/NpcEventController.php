<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcEvent;
use App\Models\NpcPart;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Exception;
use App\Models\NpcDeliveryGroup;
use App\Models\NpcCustomerCategory;

class NpcEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = \App\Models\NpcEvent::with(['masterEvent.customer', 'masterEvent.vehicleModel', 'customerCategory', 'deliveryGroup'])->latest()->paginate(10);
        return view('npc_events.index', compact('events'));
    }

    public function create()
    {
        $customers = \App\Models\Customer::orderBy('name')->get();
        // The models will be fetched dynamically via JS internally in the view
        
        // Ambil data Master Delivery Target
        $delivery_targets = \App\Models\NpcDeliveryTarget::where('is_active', true)->orderBy('target_name')->get();

        // Ambil data Grup Pengiriman
        $delivery_groups = NpcDeliveryGroup::orderBy('name')->get();

        // Ambil data Master Checkpoint untuk Mapping MGM per Part
        $checkpoints = \App\Models\NpcMasterCheckpoint::where('is_active', true)->orderBy('point_number')->get();

        return view('npc_events.create', compact('customers', 'delivery_targets', 'delivery_groups', 'checkpoints'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required', // Needed for flow Validation
            'model_id' => 'required', // Needed to validate parts
            'master_event_id' => 'required|exists:npc_master_events,id',
            'customer_category_id' => 'required|exists:npc_customer_categories,id',
            'delivery_group_id' => 'required|exists:npc_delivery_groups,id',
            'delivery_to' => 'nullable|string|max:255',
            'parts' => 'required|array|min:1',
            'parts.*.po_no' => 'required|string',
            'parts.*.part_no' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::exists('products', 'part_no')->where('model_id', $request->model_id)
            ],
            'parts.*.part_name' => 'nullable|string',
            'parts.*.qty' => 'required|integer|min:1',
            'parts.*.delivery_date' => 'required|date',
            'parts.*.checkpoints' => 'nullable|array'
        ], [
            'parts.*.part_no.exists' => 'Salah satu Part Number yang Anda masukkan tidak valid atau bukan merupakan part dari Model tersebut.'
        ]);

        $event = \App\Models\NpcEvent::create([
            'master_event_id' => $request->master_event_id,
            'customer_category_id' => $request->customer_category_id,
            'delivery_group_id' => $request->delivery_group_id,
            'delivery_to' => $request->delivery_to,
        ]);

        foreach ($request->parts as $partData) {
            // Coba cari produk berdasarkan part_no
            $product = \App\Models\Product::where('part_no', $partData['part_no'])->first();
            $processName = null;
            $departmentName = 'PUD';

            if ($product) {
                // Ambil master routing urutan pertama
                $routing = \App\Models\NpcMasterRouting::with('process')
                            ->where('part_id', $product->id)
                            ->orderBy('sequence_order', 'asc')
                            ->first();

                if ($routing && $routing->process) {
                    $processName = $routing->process->process_name;
                    $departmentName = $routing->process->department ?? 'PUD';
                }
            }

            // 1. Build PO
            $po = \App\Models\NpcPurchaseOrder::firstOrCreate([
                'npc_event_id' => $event->id,
                'po_no' => $partData['po_no']
            ]);

            // 2. Build Part Details
            $part = \App\Models\NpcPart::create([
                'npc_purchase_order_id' => $po->id,
                'product_id' => $product ? $product->id : null,
                'qty' => $partData['qty'],
                'delivery_date' => $partData['delivery_date'],
                'status' => 'PO_REGISTERED',
            ]);

            // 3. Build Initial Part Process
            if (isset($routing) && $routing && $routing->process) {
                \App\Models\NpcPartProcess::create([
                    'npc_part_id' => $part->id,
                    'process_id' => $routing->process_id,
                    'sequence_order' => $routing->sequence_order,
                    'status' => 'WAITING'
                ]);
            }

            // Sync Mapping MGM Checkpoints
            if (!empty($partData['checkpoints'])) {
                $part->checkpoints()->sync($partData['checkpoints']);
            }
        }

        return redirect()->route('events.index')->with('success', 'Event, PO, dan Parts berhasil ditambahkan.');
    }

    public function edit(\App\Models\NpcEvent $event)
    {
        $event->load('masterEvent');
        $masterCustomerId = optional($event->masterEvent)->customer_id;
        $masterModelId = optional($event->masterEvent)->model_id;
        
        $customers = \App\Models\Customer::orderBy('name')->get();
        $models = \App\Models\VehicleModel::where('customer_id', $masterCustomerId)->orderBy('name')->get();
        $master_events = \App\Models\NpcMasterEvent::where('model_id', $masterModelId)->orderBy('name')->get();
        
        $customer_categories = NpcCustomerCategory::where('customer_id', $masterCustomerId)->orderBy('name')->get();
        $delivery_groups = NpcDeliveryGroup::orderBy('name')->get();

        $delivery_targets = \App\Models\NpcDeliveryTarget::where('is_active', true)->orderBy('target_name')->get();

        return view('npc_events.edit', compact('event', 'customers', 'models', 'master_events', 'customer_categories', 'delivery_groups', 'delivery_targets', 'masterCustomerId', 'masterModelId'));
    }

    public function update(Request $request, \App\Models\NpcEvent $event)
    {
        $request->validate([
            'master_event_id' => 'required|exists:npc_master_events,id',
            'customer_category_id' => 'required|exists:npc_customer_categories,id',
            'delivery_group_id' => 'required|exists:npc_delivery_groups,id',
            'delivery_to' => 'nullable|string|max:255',
        ]);

        $event->update([
            'master_event_id' => $request->master_event_id,
            'customer_category_id' => $request->customer_category_id,
            'delivery_group_id' => $request->delivery_group_id,
            'delivery_to' => $request->delivery_to,
        ]);

        return redirect()->route('events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(\App\Models\NpcEvent $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }

    public function importForm()
    {
        $customers = \App\Models\Customer::orderBy('name')->get();
        $delivery_targets = \App\Models\NpcDeliveryTarget::where('is_active', true)->orderBy('target_name')->get();
        $delivery_groups = NpcDeliveryGroup::orderBy('name')->get();
        return view('npc_events.import', compact('customers', 'delivery_targets', 'delivery_groups'));
    }

    public function importData(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'model_id' => 'required',
            'master_event_id' => 'required',
            'customer_category_id' => 'required',
            'delivery_group_id' => 'required',
            'delivery_to' => 'nullable|string|max:255',
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $event = NpcEvent::create([
                'master_event_id' => $request->master_event_id,
                'customer_category_id' => $request->customer_category_id,
                'delivery_group_id' => $request->delivery_group_id,
                'delivery_to' => $request->delivery_to,
            ]);

            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Skip Header
            $headers = array_shift($rows);

            $importedCount = 0;
            foreach ($rows as $row) {
                if (empty($row[1])) continue; // Skip empty part no

                $deliveryDate = null;
                if (!empty($row[4])) {
                    try {
                        if (is_numeric($row[4])) {
                            $deliveryDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[4])->format('Y-m-d');
                        } else {
                            $deliveryDate = Carbon::parse($row[4])->format('Y-m-d');
                        }
                    } catch (Exception $e) {
                        $deliveryDate = now()->format('Y-m-d');
                    }
                }

                $processString = $row[5] ?? 'GR.1';
                // Smart Mapping Department via Master Process
                $procStr = (string)$row[5];
                $processObj = \App\Models\NpcProcess::where('process_name', 'LIKE', '%' . $procStr . '%')->first();
                $department = $processObj ? $processObj->department : 'PUD';

                $partName = $row[2] ?? '-';
                if($partName === '-' || empty($partName)) {
                    $product = \App\Models\Product::where('part_no', $row[1])->first();
                    if($product) $partName = $product->part_name;
                }

                $po = \App\Models\NpcPurchaseOrder::firstOrCreate([
                    'npc_event_id' => $event->id,
                    'po_no' => $row[0] ?? optional($event->masterEvent)->name
                ]);
                
                $product = \App\Models\Product::where('part_no', $row[1])->first();

                $part = NpcPart::create([
                    'npc_purchase_order_id' => $po->id,
                    'product_id' => $product ? $product->id : null,
                    'qty' => (int) ($row[3] ?? 1),
                    'delivery_date' => $deliveryDate,
                    'status' => 'WAITING_DEPT_CONFIRM',
                ]);
                
                if ($processObj) {
                    \App\Models\NpcPartProcess::create([
                        'npc_part_id' => $part->id,
                        'process_id' => $processObj->id,
                        'sequence_order' => 1,
                        'status' => 'WAITING'
                    ]);
                }
                $importedCount++;
            }

            return redirect()->route('events.index')->with('success', "Event dibuat dan $importedCount Part(s) berhasil di-import!");

        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }
}
