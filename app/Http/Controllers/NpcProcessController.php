<?php

namespace App\Http\Controllers;

use App\Models\NpcProcess;
use App\Models\NpcDepartment;
use Illuminate\Http\Request;

class NpcProcessController extends Controller
{
    public function index()
    {
        $processes = NpcProcess::orderBy('process_name', 'asc')->get();
        return view('master.processes.index', compact('processes'));
    }

    public function create()
    {
        $departments = NpcDepartment::where('is_active', true)->orderBy('name')->get();
        return view('master.processes.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'process_name' => 'required|string|max:255|unique:npc_processes',
            'department'   => 'required|exists:npc_departments,name',
        ]);

        NpcProcess::create($request->all());

        return redirect()->route('master.processes.index')->with('success', 'Master Process berhasl ditambahkan.');
    }

    public function edit(NpcProcess $process)
    {
        $departments = NpcDepartment::where('is_active', true)->orderBy('name')->get();
        return view('master.processes.edit', compact('process', 'departments'));
    }

    public function update(Request $request, NpcProcess $process)
    {
        $request->validate([
            'process_name' => 'required|string|max:255|unique:npc_processes,process_name,' . $process->id,
            'department'   => 'required|exists:npc_departments,name',
        ]);

        $process->update($request->all());

        return redirect()->route('master.processes.index')->with('success', 'Master Process berhasil diperbarui.');
    }

    public function destroy(NpcProcess $process)
    {
        $process->delete();
        return redirect()->route('master.processes.index')->with('success', 'Master Process berhasil dihapus.');
    }
}
