<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\NpcMasterStdPart;

class NpcMasterStdPartController extends Controller
{
    public function index()
    {
        $stdParts = NpcMasterStdPart::orderBy('name', 'asc')->paginate(20);
        return view('master.std_parts.index', compact('stdParts'));
    }

    public function create()
    {
        return view('master.std_parts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        NpcMasterStdPart::create([
            'name' => $request->name,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('master.std-parts.index')->with('success', 'STD Part created successfully.');
    }

    public function edit(NpcMasterStdPart $std_part)
    {
        return view('master.std_parts.edit', compact('std_part'));
    }

    public function update(Request $request, NpcMasterStdPart $std_part)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        $std_part->update([
            'name' => $request->name,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('master.std-parts.index')->with('success', 'STD Part updated successfully.');
    }

    public function destroy(NpcMasterStdPart $std_part)
    {
        $std_part->delete();
        return redirect()->route('master.std-parts.index')->with('success', 'STD Part deleted successfully.');
    }
}
