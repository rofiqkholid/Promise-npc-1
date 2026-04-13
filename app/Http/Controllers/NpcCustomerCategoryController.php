<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcCustomerCategory;
use App\Models\Customer;
use App\Models\NpcInternalCategory;

class NpcCustomerCategoryController extends Controller
{
    public function index()
    {
        $categories = NpcCustomerCategory::with(['customer', 'internalCategory'])->latest()->get();
        return view('master.customer_categories.index', compact('categories'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $internalCategories = NpcInternalCategory::orderBy('name')->get();
        return view('master.customer_categories.create', compact('customers', 'internalCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'internal_category_id' => 'required|exists:npc_internal_categories,id',
            'name' => 'required|string|max:255'
        ]);
        NpcCustomerCategory::create($request->all());
        return redirect()->route('master.customer-categories.index')->with('success', 'Mapping Kategori Customer berhasil ditambahkan.');
    }

    public function edit(NpcCustomerCategory $customerCategory)
    {
        $customers = Customer::orderBy('name')->get();
        $internalCategories = NpcInternalCategory::orderBy('name')->get();
        return view('master.customer_categories.edit', compact('customerCategory', 'customers', 'internalCategories'));
    }

    public function update(Request $request, NpcCustomerCategory $customerCategory)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'internal_category_id' => 'required|exists:npc_internal_categories,id',
            'name' => 'required|string|max:255'
        ]);
        $customerCategory->update($request->all());
        return redirect()->route('master.customer-categories.index')->with('success', 'Mapping Kategori Customer berhasil diperbarui.');
    }

    public function destroy(NpcCustomerCategory $customerCategory)
    {
        $customerCategory->delete();
        return redirect()->route('master.customer-categories.index')->with('success', 'Mapping Kategori Customer berhasil dihapus.');
    }
}
