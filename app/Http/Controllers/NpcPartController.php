<?php

namespace App\Http\Controllers;

use App\Models\NpcEvent;
use App\Models\NpcPart;
use Illuminate\Http\Request;

class NpcPartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(NpcEvent $event)
    {
        $parts = $event->parts()->latest()->paginate(10);
        return view('npc_parts.index', compact('event', 'parts'));
    }

    public function create(NpcEvent $event)
    {
        $processes = \App\Models\NpcProcess::orderBy('process_name')->get();
        return view('npc_parts.create', compact('event', 'processes'));
    }

    public function store(Request $request, \App\Models\NpcEvent $event)
    {
        $request->validate([
            'po_no' => 'required|string|max:255',
            'part_no' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::exists('products', 'part_no')->where('model_id', $event->model_id)
            ],
            'part_name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'delivery_date' => 'required|date',
            'process' => 'required|string|max:255',
        ], [
            'part_no.exists' => 'Part Number yang Anda masukkan tidak valid atau bukan merupakan part dari Model event ini.'
        ]);

        $po = \App\Models\NpcPurchaseOrder::firstOrCreate([
            'npc_event_id' => $event->id,
            'po_no' => $request->po_no
        ]);

        $product = \App\Models\Product::where('part_no', $request->part_no)->first();

        $part = \App\Models\NpcPart::create([
            'npc_purchase_order_id' => $po->id,
            'product_id' => $product ? $product->id : null,
            'qty' => $request->qty,
            'delivery_date' => $request->delivery_date,
            'status' => 'PO_REGISTERED'
        ]);

        $processRecord = \App\Models\NpcProcess::where('process_name', $request->process)->first();
        if ($processRecord) {
            \App\Models\NpcPartProcess::create([
                'npc_part_id' => $part->id,
                'process_id' => $processRecord->id,
                'sequence_order' => 1,
                'status' => 'WAITING'
            ]);
        }

        return redirect()->route('events.parts.index', $event->id)->with('success', 'Part / Item added to event successfully.');
    }

    public function edit(NpcEvent $event, NpcPart $part)
    {
        $processes = \App\Models\NpcProcess::orderBy('process_name')->get();
        return view('npc_parts.edit', compact('event', 'part', 'processes'));
    }

    public function update(Request $request, \App\Models\NpcEvent $event, \App\Models\NpcPart $part)
    {
        $request->validate([
            'po_no' => 'required|string|max:255',
            'part_no' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::exists('products', 'part_no')->where('model_id', $event->model_id)
            ],
            'part_name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'delivery_date' => 'required|date',
            'actual_delivery' => 'nullable|date',
            'department' => 'required|exists:npc_departments,name',
            'process' => 'required|string|max:255',
            'status' => 'required|in:WAITING_DEPT_CONFIRM,WAITING_QE_CHECK,WAITING_MGM_CHECK,FINISHED,CLOSED,OPEN',
            'condition' => 'nullable|string',
        ], [
            'part_no.exists' => 'Part Number yang Anda masukkan tidak valid atau bukan merupakan part dari Model event ini.'
        ]);

        $po = \App\Models\NpcPurchaseOrder::firstOrCreate([
            'npc_event_id' => $event->id,
            'po_no' => $request->po_no
        ]);

        $product = \App\Models\Product::where('part_no', $request->part_no)->first();

        $part->update([
            'npc_purchase_order_id' => $po->id,
            'product_id' => $product ? $product->id : null,
            'qty' => $request->qty,
            'delivery_date' => $request->delivery_date,
            'actual_delivery' => $request->actual_delivery,
            'status' => $request->status,
            'condition' => $request->condition
        ]);

        return redirect()->route('events.parts.index', $event->id)->with('success', 'Part updated successfully.');
    }

    public function destroy(\App\Models\NpcEvent $event, \App\Models\NpcPart $part)
    {
        $part->delete();
        return redirect()->route('events.parts.index', $event->id)->with('success', 'Part deleted successfully.');
    }
}
