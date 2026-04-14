<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcPart;
use App\Models\NpcPartProcess;
use App\Models\NpcProcess;
use App\Models\NpcDepartment;

class NpcPartProcessController extends Controller
{
    /**
     * Show the form for editing the specified routing/processes of a part.
     */
    public function edit(NpcPart $part)
    {
        $part->load('processes.process.department', 'purchaseOrder.event.masterEvent');

        // Jika belum ada proses, ambil dari NpcMasterRouting sebagai default
        if ($part->processes->isEmpty()) {
            $product = \App\Models\Product::where('part_no', $part->part_no)->first();
            if ($product) {
                $masterRoutings = \App\Models\NpcMasterRouting::with('process')
                    ->where('part_id', $product->id)
                    ->orderBy('sequence_order', 'asc')
                    ->get();
                
                $defaultProcesses = collect();
                foreach ($masterRoutings as $mr) {
                    if ($mr->process) {
                        $defaultProcesses->push([
                            'process_name' => $mr->process->process_name,
                            'department' => $mr->process->department,
                            'target_completion_date' => '',
                            'sequence_order' => $mr->sequence_order
                        ]);
                    }
                }
                
                // Gunakan mapping bawaan / setrika data menjadi format yang dipahami Javascript existingData
                if ($defaultProcesses->isNotEmpty()) {
                    $part->setRelation('processes', $defaultProcesses);
                }
            }
        } else {
            // Map the loaded processes to include process_name and department for the Javascript frontend
            $part->processes->transform(function ($process) {
                $process->process_name = optional($process->process)->process_name;
                $process->department = optional(optional($process->process)->department)->name;
                return $process;
            });
        }

        $masterProcesses = tap(NpcProcess::orderBy('process_name')->get(), function ($q) {
            $q->transform(function ($p) {
                // Ensure legacy standard naming match
                if(!$p->department && strpos(strtoupper($p->process_name), 'GR') !== false) $p->department = 'PUD';
                if(!$p->department && strpos(strtoupper($p->process_name), 'PR') !== false) $p->department = 'ME';
                return $p;
            });
        });
        
        $departments = NpcDepartment::where('is_active', true)->orderBy('name')->get();

        return view('npc_parts.routing', compact('part', 'masterProcesses', 'departments'));
    }

    /**
     * Update the specified routing in storage.
     */
    public function update(Request $request, NpcPart $part)
    {
        $request->validate([
            'routing' => 'nullable|array',
            'routing.*.process_name' => 'required|string',
            'routing.*.department' => 'required|string',
            'routing.*.target_completion_date' => 'required|date',
            'routing.*.sequence_order' => 'required|integer',
            'qc_target_date' => 'nullable|date',
            'mgm_target_date' => 'nullable|date',
        ]);

        // Clear existing un-finished processes or resync all if you prefer pure overwrite
        // For safety, we should only allow editing if they haven't started, or smartly merge.
        // For simplicity now: delete all and recreate (assuming this is done during planning phase)
        $part->processes()->delete();

        if ($request->has('routing') && !empty($request->routing)) {
            foreach ($request->routing as $routeData) {
                $process = \App\Models\NpcProcess::where('process_name', $routeData['process_name'])->first();
                NpcPartProcess::create([
                    'npc_part_id' => $part->id,
                    'process_id' => $process ? $process->id : null,
                    'target_completion_date' => $routeData['target_completion_date'],
                    'sequence_order' => $routeData['sequence_order'],
                    'status' => 'WAITING'
                ]);
            }
            
            if($part->status === 'PO_REGISTERED') {
                $part->update([
                    'status' => 'WAITING_DEPT_CONFIRM',
                    'qc_target_date' => $request->qc_target_date,
                    'mgm_target_date' => $request->mgm_target_date,
                ]);
            } else {
                $part->update([
                    'qc_target_date' => $request->qc_target_date,
                    'mgm_target_date' => $request->mgm_target_date,
                ]);
            }
        }

        return redirect()->route('tracking.index')->with('success', "Routing process untuk part {$part->part_no} berhasil diperbarui.");
    }
}
