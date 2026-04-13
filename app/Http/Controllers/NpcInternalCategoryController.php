<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcInternalCategory;

class NpcInternalCategoryController extends Controller
{
    public function index()
    {
        $categories = NpcInternalCategory::latest()->get();
        return view('master.internal_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('master.internal_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:npc_internal_categories']);
        NpcInternalCategory::create($request->all());
        return redirect()->route('master.internal-categories.index')->with('success', 'Kategori Internal berhasil ditambahkan.');
    }

    public function edit(NpcInternalCategory $internalCategory)
    {
        return view('master.internal_categories.edit', compact('internalCategory'));
    }

    public function update(Request $request, NpcInternalCategory $internalCategory)
    {
        $request->validate(['name' => 'required|string|max:255|unique:npc_internal_categories,name,' . $internalCategory->id]);
        $internalCategory->update($request->all());
        return redirect()->route('master.internal-categories.index')->with('success', 'Kategori Internal berhasil diperbarui.');
    }

    public function destroy(NpcInternalCategory $internalCategory)
    {
        $internalCategory->delete();
        return redirect()->route('master.internal-categories.index')->with('success', 'Kategori Internal berhasil dihapus.');
    }
}
