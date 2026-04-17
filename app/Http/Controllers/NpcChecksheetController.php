<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcPart;
use App\Models\NpcChecksheet;
use App\Models\NpcChecksheetDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class NpcChecksheetController extends Controller
{
    /**
     * Show the form for creating/editing the QC or MGM checksheet.
     */
    public function create(NpcPart $part)
    {
        $part->load('product.mappedCheckpoints.masterCheckpoint');
        $checksheet = $part->checksheet;
        if ($checksheet) {
            $checksheet->load('details');
        }
        
        if (!$checksheet) {
            $checksheet = NpcChecksheet::create([
                'npc_part_id' => $part->id,
                'final_result' => 'Pending'
            ]);

            $this->generateChecksheetDetails($checksheet, $part);
        } elseif ($checksheet->details->isEmpty()) {
            $this->generateChecksheetDetails($checksheet, $part);
        }

        return redirect()->route('checksheets.edit', $checksheet->id);
    }

    private function generateChecksheetDetails(NpcChecksheet $checksheet, NpcPart $part)
    {
        if ($part->product && $part->product->mappedCheckpoints->isNotEmpty()) {
            $checkpoints = $part->product->mappedCheckpoints;
            foreach ($checkpoints as $mapped) {
                if ($mapped->masterCheckpoint) {
                    NpcChecksheetDetail::create([
                        'npc_checksheet_id' => $checksheet->id,
                        'point_check'       => $mapped->masterCheckpoint->check_item,
                        'standard'          => $mapped->custom_standard ?? $mapped->masterCheckpoint->standard,
                    ]);
                }
            }
        } else {
            // Fallback to ALL active master checkpoints
            $checkpoints = \App\Models\NpcMasterCheckpoint::where('is_active', true)->orderBy('point_number')->get();
            foreach ($checkpoints as $mappedPoint) {
                NpcChecksheetDetail::create([
                    'npc_checksheet_id' => $checksheet->id,
                    'point_check'       => $mappedPoint->check_item,
                    'standard'          => null,
                ]);
            }
        }
    }

    /**
     * Display the checksheet form.
     */
    public function edit(NpcChecksheet $checksheet)
    {
        $checksheet->load('details', 'npcPart.checkpoints', 'qeChecker', 'mgmChecker');
        $part = $checksheet->npcPart;

        return view('npc_checksheets.edit', compact('checksheet', 'part'));
    }

    /**
     * Store checksheet inputs (handles both QE/QC and MGM roles).
     */
    public function update(Request $request, NpcChecksheet $checksheet)
    {
        $request->validate([
            'role' => 'required|in:QC,MGM',
        ]);

        $part = $checksheet->npcPart;

        if ($request->role === 'QC') {
            $request->validate([
                'accuracy_percentage' => 'required|numeric|min:0|max:100',
                'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240'
            ]);

            $updateData = [
                'accuracy_percentage' => $request->accuracy_percentage,
                'qe_checked_by' => auth()->id() ?? 1,
                'qe_check_date' => Carbon::now()
            ];

            if ($request->hasFile('attachment_file')) {
                // Delete old file if exists
                if ($checksheet->attachment_path) {
                    Storage::disk('public')->delete($checksheet->attachment_path);
                }
                
                $path = $request->file('attachment_file')->store('npc_checksheets', 'public');
                $updateData['attachment_path'] = $path;
            }

            $checksheet->update($updateData);

            // Move to next step
            if ($part->status === 'WAITING_QE_CHECK') {
                $part->update(['status' => 'WAITING_MGM_CHECK']);
            }

            return redirect()->route('tracking.index')->with('success', "Data QC (Accuracy: {$request->accuracy_percentage}%) berhasil disimpan.");

        } elseif ($request->role === 'MGM') {
            $request->validate([
                'final_result' => 'required|in:OK,NG,Need Improvement',
                'details' => 'array'
            ]);

            // Update individual checkpoint statuses
            foreach ($request->input('details', []) as $id => $data) {
                $detail = NpcChecksheetDetail::find($id);
                if ($detail && $detail->npc_checksheet_id == $checksheet->id) {
                    $detail->update([
                        'row_result' => $data['row_result'] ?? null,
                    ]);
                }
            }

            $checksheet->update([
                'final_result' => $request->final_result,
                'mgm_checked_by' => auth()->id() ?? 1, // Fallback if auth missing
                'mgm_check_date' => Carbon::now()
            ]);

            // Process new history problems
            if ($request->has('new_history_problems') && is_array($request->new_history_problems)) {
                $problems = array_filter($request->new_history_problems, function($val) {
                    return !empty(trim($val));
                });

                if (!empty($problems) && $part->product) {
                    $insertData = [];
                    foreach ($problems as $probDesc) {
                        $insertData[] = [
                            'product_id' => $part->product->id,
                            'problem_description' => trim($probDesc),
                            'npc_part_id_finder' => $part->id,
                            'created_by' => auth()->id() ?? 1,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                    \App\Models\ProductHistoryProblem::insert($insertData);
                }
            }

            // Finish the part production
            if ($part->status === 'WAITING_MGM_CHECK') {
                $part->update(['status' => 'FINISHED']);
            }

            return redirect()->route('tracking.index')->with('success', 'MGM Checksheet berhasil divalidasi dan disimpan.');
        }

        return redirect()->route('tracking.index');
    }
}
