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
        $events = \App\Models\NpcEvent::with(['customer', 'vehicleModel', 'customerCategory', 'deliveryGroup'])->latest()->paginate(10);
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
            'event_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'model_id' => 'required',
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
            'event_name' => $request->event_name,
            'customer_id' => $request->customer_id,
            'model_id' => $request->model_id,
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

            $part = \App\Models\NpcPart::create([
                'npc_event_id' => $event->id,
                'po_no' => $partData['po_no'],
                'part_no' => $partData['part_no'],
                'part_name' => $partData['part_name'] ?? '-', 
                'qty' => $partData['qty'],
                'delivery_date' => $partData['delivery_date'],
                'process' => $processName,
                'department' => $departmentName,
                'status' => 'PO_REGISTERED',
            ]);

            // Sync Mapping MGM Checkpoints
            if (!empty($partData['checkpoints'])) {
                $part->checkpoints()->sync($partData['checkpoints']);
            }
        }

        return redirect()->route('events.index')->with('success', 'Event, PO, dan Parts berhasil ditambahkan.');
    }

    public function edit(\App\Models\NpcEvent $event)
    {
        $customers = \App\Models\Customer::orderBy('name')->get();
        // Get models for the CURRENT customer to populate the initial dropdown state
        $models = \App\Models\VehicleModel::where('customer_id', $event->customer_id)->orderBy('name')->get();
        
        $customer_categories = NpcCustomerCategory::where('customer_id', $event->customer_id)->orderBy('name')->get();
        $delivery_groups = NpcDeliveryGroup::orderBy('name')->get();

        // Ambil data Master Delivery Target
        $delivery_targets = \App\Models\NpcDeliveryTarget::where('is_active', true)->orderBy('target_name')->get();

        return view('npc_events.edit', compact('event', 'customers', 'models', 'customer_categories', 'delivery_groups', 'delivery_targets'));
    }

    public function update(Request $request, \App\Models\NpcEvent $event)
    {
        $request->validate([
            'event_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'model_id' => 'required|exists:models,id',
            'customer_category_id' => 'required|exists:npc_customer_categories,id',
            'delivery_group_id' => 'required|exists:npc_delivery_groups,id',
            'delivery_to' => 'nullable|string|max:255',
        ]);

        $event->update($request->all());

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
            'customer_category_id' => 'required',
            'delivery_group_id' => 'required',
            'event_name' => 'required|string|max:255',
            'delivery_to' => 'nullable|string|max:255',
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $event = NpcEvent::create([
                'customer_id' => $request->customer_id,
                'model_id' => $request->model_id,
                'customer_category_id' => $request->customer_category_id,
                'delivery_group_id' => $request->delivery_group_id,
                'event_name' => $request->event_name,
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

                NpcPart::create([
                    'npc_event_id' => $event->id,
                    'po_no' => $row[0] ?? $request->event_name,
                    'part_no' => $row[1],
                    'part_name' => $partName,
                    'qty' => (int) ($row[3] ?? 1),
                    'delivery_date' => $deliveryDate,
                    'department' => $department,
                    'process' => $processString,
                    'status' => 'WAITING_DEPT_CONFIRM',
                ]);
                $importedCount++;
            }

            return redirect()->route('events.index')->with('success', "Event dibuat dan $importedCount Part(s) berhasil di-import!");

        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }
}
