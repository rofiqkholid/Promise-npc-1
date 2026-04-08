<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductionTrackingController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $statusParam = $request->query('status', 'all');

        $query = \App\Models\NpcPart::with(['npcEvent', 'processes', 'checkpoints', 'checksheet'])->latest();

        if ($statusParam !== 'all') {
            // "Kamar Task" logic: show current status + ALL previous statuses as "Upcoming"
            switch($statusParam) {
                case 'WAITING_DEPT_CONFIRM':
                    $query->whereIn('status', ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM']);
                    break;
                case 'WAITING_QE_CHECK':
                    $query->whereIn('status', ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK']);
                    break;
                case 'WAITING_MGM_CHECK':
                    $query->whereIn('status', ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK', 'WAITING_MGM_CHECK']);
                    break;
                case 'FINISHED':
                    $query->whereIn('status', ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK', 'WAITING_MGM_CHECK', 'FINISHED']);
                    break;
                default:
                    $query->where('status', $statusParam);
                    break;
            }
        }

        $parts = $query->paginate(15);

        return view('tracking.index', compact('parts', 'statusParam'));
    }

    public function updateStatus(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        $request->validate([
            'status' => 'required|in:PO_REGISTERED,WAITING_DEPT_CONFIRM,IN_PRODUCTION,WAITING_QE_CHECK,WAITING_MGM_CHECK,FINISHED,CLOSED',
            'actual_completion_date' => 'nullable|date',
            'production_notes' => 'nullable|string|max:500',
        ]);

        $updateData = ['status' => $request->status];

        if ($request->status === 'WAITING_QE_CHECK') {
            if ($request->filled('actual_completion_date')) {
                $updateData['actual_completion_date'] = $request->actual_completion_date;
            }
            if ($request->filled('production_notes')) {
                $updateData['production_notes'] = $request->production_notes;
            }
        }

        $part->update($updateData);

        return back()->with('success', 'Status Part berhasil diperbarui.');
    }

    public function deliver(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        $part->update([
            'status' => 'CLOSED',
            'actual_delivery' => \Carbon\Carbon::now()
        ]);

        return back()->with('success', 'Part berhasil dikirim ke customer dan ditutup.');
    }
}
