<?php

namespace App\Http\Controllers;

use App\Models\NpcMasterRouting;
use App\Models\Product;
use App\Models\NpcProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NpcMasterRoutingController extends Controller
{
    public function index()
    {
        $routings = NpcMasterRouting::with(['part', 'process'])
            ->select('part_id')
            ->groupBy('part_id')
            ->paginate(10);
            
        // We grouped by part_id, so now we fetch the processes for each part
        foreach ($routings as $routing) {
            $routing->processes = NpcMasterRouting::with('process')
                ->where('part_id', $routing->part_id)
                ->orderBy('sequence_order')
                ->get();
        }

        return view('master.routings.index', compact('routings'));
    }

    public function create()
    {
        $processes = NpcProcess::orderBy('process_name')->get();
        return view('master.routings.create', compact('processes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'part_id' => 'required|exists:products,id',
            'process_ids' => 'required|array|min:1',
            'process_ids.*' => 'required|exists:npc_processes,id',
        ]);

        DB::transaction(function () use ($request) {
            // Hapus yang lama jika sudah ada (opsional jika ini create)
            NpcMasterRouting::where('part_id', $request->part_id)->delete();

            foreach ($request->process_ids as $index => $processId) {
                NpcMasterRouting::create([
                    'part_id' => $request->part_id,
                    'process_id' => $processId,
                    'sequence_order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('master.routings.index')->with('success', 'Master Routing berhasil disimpan.');
    }

    public function edit($part_id)
    {
        $part = Product::findOrFail($part_id);
        $routings = NpcMasterRouting::where('part_id', $part_id)->orderBy('sequence_order')->get();
        $processes = NpcProcess::orderBy('process_name')->get();

        return view('master.routings.edit', compact('part', 'routings', 'processes'));
    }

    public function update(Request $request, $part_id)
    {
        $request->validate([
            'process_ids' => 'required|array|min:1',
            'process_ids.*' => 'required|exists:npc_processes,id',
        ]);

        DB::transaction(function () use ($request, $part_id) {
            NpcMasterRouting::where('part_id', $part_id)->delete();

            foreach ($request->process_ids as $index => $processId) {
                NpcMasterRouting::create([
                    'part_id' => $part_id,
                    'process_id' => $processId,
                    'sequence_order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('master.routings.index')->with('success', 'Master Routing berhasil diperbarui.');
    }

    public function destroy($part_id)
    {
        NpcMasterRouting::where('part_id', $part_id)->delete();
        return redirect()->route('master.routings.index')->with('success', 'Master Routing berhasil dihapus.');
    }
}
