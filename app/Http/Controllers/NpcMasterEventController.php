<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\NpcMasterEvent;
use App\Models\Customer;
use App\Models\VehicleModel;

class NpcMasterEventController extends Controller
{
    public function index()
    {
        $masterEvents = NpcMasterEvent::with(['customer', 'vehicleModel'])->latest()->paginate(10);
        return view('master.events.index', compact('masterEvents'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('master.events.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'model_id' => 'required|exists:models,id',
            'name' => 'required|string|max:255',
        ]);

        NpcMasterEvent::create($request->all());

        return redirect()->route('master.events.index')->with('success', 'Master Event berhasil dibuat.');
    }

    public function edit(NpcMasterEvent $event)
    {
        $customers = Customer::orderBy('name')->get();
        // The model list is usually loaded via AJAX based on customer, but for edit we might supply all models for that customer
        $models = VehicleModel::where('customer_id', $event->customer_id)->orderBy('name')->get();
        
        return view('master.events.edit', compact('event', 'customers', 'models'));
    }

    public function update(Request $request, NpcMasterEvent $event)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'model_id' => 'required|exists:models,id',
            'name' => 'required|string|max:255',
        ]);

        $event->update($request->all());

        return redirect()->route('master.events.index')->with('success', 'Master Event berhasil diperbarui.');
    }

    public function destroy(NpcMasterEvent $event)
    {
        // Protect deletion if there are transactions
        if ($event->transactions()->count() > 0) {
            return redirect()->route('master.events.index')->with('error', 'Tidak bisa menghapus Master Event yang sudah digunakan dalam Transaksi PO.');
        }

        $event->delete();
        return redirect()->route('master.events.index')->with('success', 'Master Event berhasil dihapus.');
    }
}
