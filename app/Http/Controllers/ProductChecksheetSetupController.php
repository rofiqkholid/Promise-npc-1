<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\NpcMasterCheckpoint;
use App\Models\ProductCheckpoint;

class ProductChecksheetSetupController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('mappedCheckpoints', 'customer', 'vehicleModel')->orderBy('part_no');
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                  ->orWhere('part_name', 'like', "%{$search}%");
            });
        }
        
        $products = $query->paginate(20);
        return view('master.product_checksheets.index', compact('products'));
    }

    public function edit(Product $product)
    {
        $masterPoints = NpcMasterCheckpoint::where('is_active', true)->orderBy('point_number')->get();
        // Load existing mapping if any
        $product->load('mappedCheckpoints');
        
        $mappedData = [];
        foreach ($product->mappedCheckpoints as $mc) {
            $mappedData[$mc->npc_master_checkpoint_id] = $mc->custom_standard;
        }

        // The user says "default check all" if empty!
        $isFirstTime = $product->mappedCheckpoints->isEmpty();

        return view('tracking.checksheet_setup', compact('product', 'masterPoints', 'mappedData', 'isFirstTime'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'points' => 'array',
        ]);

        // Delete old mapping
        ProductCheckpoint::where('product_id', $product->id)->delete();

        // Insert new ones
        if ($request->has('points') && is_array($request->points)) {
            $insertData = [];
            foreach ($request->points as $masterId => $data) {
                if (isset($data['is_checked']) && $data['is_checked'] == '1') {
                    $insertData[] = [
                        'product_id' => $product->id,
                        'npc_master_checkpoint_id' => $masterId,
                        'custom_standard' => $data['custom_standard'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (!empty($insertData)) {
                ProductCheckpoint::insert($insertData);
            }
        }

        return redirect()->route('master.checksheets.index')->with('success', 'Master Checksheet for Part ' . $product->part_no . ' successfully saved!');
    }
}
