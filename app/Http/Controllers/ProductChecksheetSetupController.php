<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\NpcMasterCheckpoint;
use App\Models\ProductCheckpoint;
use App\Models\NpcProductDetail;
use App\Models\NpcSpecChildPart;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Exception;

class ProductChecksheetSetupController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('mappedCheckpoints', 'customer', 'vehicleModel')->orderBy('part_no');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                  ->orWhere('part_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('customer_id') && $request->customer_id != '') {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('model_id') && $request->model_id != '') {
            $query->where('model_id', $request->model_id);
        }

        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'mapped') {
                $query->whereHas('mappedCheckpoints');
            } elseif ($request->status == 'unmapped') {
                $query->whereDoesntHave('mappedCheckpoints');
            }
        }
        
        $products = $query->paginate(20);

        $customers = \App\Models\Customer::orderBy('code')->get();
        $models = \App\Models\VehicleModel::orderBy('name')->get();

        return view('master.product_checksheets.index', compact('products', 'customers', 'models'));
    }

    public function edit(Product $product)
    {
        $masterPoints = NpcMasterCheckpoint::where('is_active', true)->orderBy('sequence_order')->orderBy('point_number')->get();
        // Load existing mapping if any
        $product->load('mappedCheckpoints', 'productDetail', 'specChildParts.stdPart');
        
        $mappedData = [];
        foreach ($product->mappedCheckpoints as $mc) {
            $mappedData[$mc->npc_master_checkpoint_id] = $mc->custom_standard;
        }

        // Separate material parts and std parts
        $materialParts = $product->specChildParts->where('part_type', 'MATERIAL')->values();
        $stdParts = $product->specChildParts->where('part_type', 'STD_PART')->values();
        
        // For inventory material names, we'll fetch them manually if they exist
        $materialIds = $materialParts->pluck('inventory_material_id')->filter()->toArray();
        $inventoryMaterials = [];
        if (!empty($materialIds)) {
            $invMats = \Illuminate\Support\Facades\DB::table('inv_m_material_spec')
                        ->whereIn('id', $materialIds)
                        ->get(['id', 'spec_name'])->keyBy('id');
            foreach ($invMats as $id => $mat) {
                $inventoryMaterials[$id] = $mat->spec_name;
            }
        }

        // The user says "default check all" if empty!
        $isFirstTime = $product->mappedCheckpoints->isEmpty();

        return view('tracking.checksheet_setup', compact('product', 'masterPoints', 'mappedData', 'isFirstTime', 'materialParts', 'stdParts', 'inventoryMaterials'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'points' => 'array',
            'sketch_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'material_parts' => 'nullable|array',
            'std_parts' => 'nullable|array',
        ]);

        // 1. Handle Sketch Image (untuk checksheet)
        if ($request->hasFile('sketch_image')) {
            $file = $request->file('sketch_image');
            $filename = time() . '_sketch_' . $file->getClientOriginalName();
            $path = $file->storeAs('public/checksheets/sketches', $filename);

            // Delete old if exists
            if ($product->productDetail && $product->productDetail->sketch_image_path) {
                Storage::delete($product->productDetail->sketch_image_path);
            }

            NpcProductDetail::updateOrCreate(
                ['product_id' => $product->id],
                ['sketch_image_path' => $path]
            );
        }

        // 2. Handle Spec Child Parts
        NpcSpecChildPart::where('product_id', $product->id)->delete();
        $childPartsData = [];

        // Materials
        if ($request->has('material_parts') && is_array($request->material_parts)) {
            foreach ($request->material_parts as $mat) {
                if (!empty($mat['inventory_material_id']) || !empty($mat['sequence_label'])) {
                    $childPartsData[] = [
                        'product_id' => $product->id,
                        'part_type' => 'MATERIAL',
                        'sequence_label' => $mat['sequence_label'] ?? '',
                        'inventory_material_id' => $mat['inventory_material_id'] ?? null,
                        'std_part_id' => null,
                        'thickness' => $mat['thickness'] ?? null,
                        'spec' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // STD Parts
        if ($request->has('std_parts') && is_array($request->std_parts)) {
            foreach ($request->std_parts as $std) {
                if (!empty($std['std_part_id']) || !empty($std['sequence_label'])) {
                    $childPartsData[] = [
                        'product_id' => $product->id,
                        'part_type' => 'STD_PART',
                        'sequence_label' => $std['sequence_label'] ?? '',
                        'inventory_material_id' => null,
                        'std_part_id' => $std['std_part_id'] ?? null,
                        'thickness' => null,
                        'spec' => $std['spec'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (!empty($childPartsData)) {
            NpcSpecChildPart::insert($childPartsData);
        }

        // 3. Handle Checkpoints
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
        
        activity()
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->event('updated')
            ->log('Part Checksheet Master');

        return redirect()->route('master.checksheets.index')->with('success', 'Master Checksheet for Part ' . $product->part_no . ' successfully saved!');
    }

    public function preview(Product $product)
    {
        $product->load('mappedCheckpoints.masterCheckpoint', 'productDetail', 'specChildParts.stdPart', 'docPackage.currentRevision', 'vehicleModel.customer');

        if ($product->mappedCheckpoints->isEmpty()) {
            return back()->with('error', 'Part checksheet has not been mapped yet. Cannot preview.');
        }

        $fakeChecksheet = new \App\Models\NpcChecksheet();
        $fakeChecksheet->final_result = 'Preview Mode';
        
        $details = collect();
        foreach ($product->mappedCheckpoints as $mapped) {
            if ($mapped->masterCheckpoint) {
                $detail = new \App\Models\NpcChecksheetDetail([
                    'point_check' => $mapped->masterCheckpoint->check_item,
                    'standard' => $mapped->custom_standard ?? $mapped->masterCheckpoint->standard,
                    'row_result' => null,
                    'samples' => [],
                ]);
                $details->push($detail);
            }
        }
        $fakeChecksheet->setRelation('details', $details);

        $part = null;
        $checksheet = $fakeChecksheet;

        return view('npc_checksheets.preview', compact('checksheet', 'part', 'product'));
    }

    public function importForm()
    {
        return view('master.product_checksheets.import');
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            
            // --- Sheet 1: Input Template ---
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template Import');
            
            // Set Headers
            $headers = ['PART NO', 'CHECKPOINT NUMBER', 'CUSTOM STANDARD'];
            foreach ($headers as $index => $header) {
                $column = chr(65 + $index);
                $sheet->setCellValue($column . '1', $header);
                $sheet->getStyle($column . '1')->getFont()->setBold(true);
            }
            
            // Add Sample Data
            $sampleData = [
                ['PART-001', '1', 'No Scratch'],
                ['PART-001', '2', 'Length 100mm +- 0.5'],
                ['PART-002', '1', 'Surface Smooth'],
            ];
            
            foreach ($sampleData as $rowIndex => $rowData) {
                foreach ($rowData as $columnIndex => $value) {
                    $column = chr(65 + $columnIndex);
                    $sheet->setCellValue($column . ($rowIndex + 2), $value);
                }
            }

            // --- Right Side: Master Data Reference ---
            // Set Headers for Reference starting at column E
            $sheet->setCellValue('E1', 'REFERENCE NUMBER');
            $sheet->setCellValue('F1', 'REFERENCE CHECKPOINT NAME');
            $sheet->getStyle('E1:F1')->getFont()->setBold(true);
            
            // Set light gray background for reference headers to distinguish them
            $sheet->getStyle('E1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFF0F0F0');

            // Fetch actual Master Checkpoint Data
            $masterPoints = \App\Models\NpcMasterCheckpoint::where('is_active', true)
                                ->orderBy('sequence_order')
                                ->orderBy('point_number')
                                ->get();
            
            $rowRef = 2;
            foreach ($masterPoints as $mp) {
                $sheet->setCellValue('E' . $rowRef, $mp->point_number);
                $sheet->setCellValue('F' . $rowRef, $mp->check_item);
                $rowRef++;
            }
            
            // Auto size columns
            foreach (['A', 'B', 'C', 'E', 'F'] as $colID) {
                $sheet->getColumnDimension($colID)->setAutoSize(true);
            }
            
            $writer = new Xlsx($spreadsheet);
            $fileName = 'Routing_Checksheet_Import_Template_' . date('Ymd_His') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($tempFile);
            
            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
            
        } catch (Exception $e) {
            return back()->with('error', 'Failed generating template: ' . $e->getMessage());
        }
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Skip Header
            $headers = array_shift($rows);

            $importedCount = 0;
            $partsProcessed = [];
            $rowErrors = [];
            $validRows = [];

            foreach ($rows as $index => $row) {
                if (empty($row[0])) continue; // Skip empty PART NO

                $actualRowNumber = $index + 2; // +1 for 0-index, +1 for header
                $partNo = trim($row[0]);
                $checkpointNum = trim($row[1] ?? '');
                $customStandard = trim($row[2] ?? ''); // Now in column C (index 2)

                if (empty($checkpointNum)) {
                    $rowErrors[] = "Row {$actualRowNumber}: Checkpoint Number must be provided.";
                    continue;
                }

                // 1. Resolve Product
                $product = Product::where('part_no', $partNo)->first();
                if (!$product) {
                    $rowErrors[] = "Row {$actualRowNumber}: Part No '{$partNo}' is not found in the system.";
                    continue;
                }

                // 2. Resolve Master Checkpoint
                $masterPoint = NpcMasterCheckpoint::where('point_number', $checkpointNum)->first();
                
                if (!$masterPoint) {
                    $rowErrors[] = "Row {$actualRowNumber}: Master Checkpoint not found (Num: '{$checkpointNum}').";
                    continue;
                }

                $validRows[] = [
                    'product_id' => $product->id,
                    'npc_master_checkpoint_id' => $masterPoint->id,
                    'custom_standard' => $customStandard ?: null
                ];
            }

            if (!empty($rowErrors)) {
                $displayErrors = array_slice($rowErrors, 0, 15);
                if (count($rowErrors) > 15) {
                    $displayErrors[] = "<i>...and " . (count($rowErrors) - 15) . " other errors.</i>";
                }
                $errorMsg = "<strong>Import failed due to data mismatch:</strong><ul class='list-disc pl-5 mt-2'><li>" . implode("</li><li>", $displayErrors) . "</li></ul>";
                
                return back()
                    ->with('error', 'Import failed due to data mismatches. Please check the details on the page.')
                    ->with('error_details', $errorMsg);
            }

            DB::beginTransaction();

            foreach ($validRows as $valid) {
                // 3. Delete existing checksheet for this part if not processed yet in this loop
                if (!in_array($valid['product_id'], $partsProcessed)) {
                    ProductCheckpoint::where('product_id', $valid['product_id'])->delete();
                    $partsProcessed[] = $valid['product_id'];
                }

                // 4. Insert checksheet routing
                ProductCheckpoint::create([
                    'product_id' => $valid['product_id'],
                    'npc_master_checkpoint_id' => $valid['npc_master_checkpoint_id'],
                    'custom_standard' => $valid['custom_standard'],
                ]);

                $importedCount++;
            }

            $partCount = count($partsProcessed);

            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->event('imported')
                ->log("Part Checksheet Master - $importedCount checkpoints mapped for $partCount parts");

            return redirect()->route('master.checksheets.index')->with('success', "Success! $importedCount checkpoints mapped for $partCount Part(s).");

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed processing Excel: ' . $e->getMessage());
        }
    }
}
