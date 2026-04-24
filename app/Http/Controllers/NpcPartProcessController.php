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
        $part->load('processes.process.departments', 'processes.department', 'purchaseOrder.event.customerCategory');

        // Jika belum ada proses, ambil of NpcMasterRouting sebagai default
        if ($part->processes->isEmpty()) {
            $product = $part->product;
            if ($product) {
                // Here we eager load process
                $masterRoutings = \App\Models\NpcMasterRouting::with(['process.departments', 'department'])
                    ->where('part_id', $product->id)
                    ->orderBy('sequence_order', 'asc')
                    ->get();
                
                $defaultProcesses = collect();
                foreach ($masterRoutings as $mr) {
                    if ($mr->process) {
                        $defaultDept = $mr->department ?? $mr->process->departments->first();
                        $defaultProcesses->push([
                            'process_name' => $mr->process->process_name,
                            'process_id' => $mr->process_id,
                            'department_id' => $mr->department_id ?? optional($defaultDept)->id,
                            'department_name' => optional($defaultDept)->name ?? '-',
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
                
                $mappedDept = $process->department ?? optional($process->process)->departments->first();
                $process->department_id = $process->department_id ?? optional($mappedDept)->id;
                $process->department_name = optional($mappedDept)->name ?? '-';
                return $process;
            });
        }

        $masterProcesses = tap(NpcProcess::with('departments')->orderBy('process_name')->get(), function ($q) {
            $q->transform(function ($p) {
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
            'routing.*.department_id' => 'required|exists:npc_departments,id',
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
                    'department_id' => $routeData['department_id'],
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

        return redirect()->route('tracking.index')->with('success', "Routing process for part {$part->part_no} successfully updated.");
    }
}
