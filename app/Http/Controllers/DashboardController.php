<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcPart;
use App\Models\NpcEvent;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Top Level Metrics (KPI Cards)
        $totalActiveEvents = NpcEvent::count();

        $partsInProduction = NpcPart::where('status', 'WAITING_DEPT_CONFIRM')->count();
        $pendingQc = NpcPart::where('status', 'WAITING_QE_CHECK')->count();
        $readyToDeliver = NpcPart::where('status', 'FINISHED')->count();

        $metrics = [
            'active_events' => $totalActiveEvents,
            'in_production' => $partsInProduction,
            'pending_qc' => $pendingQc,
            'ready_deliver' => $readyToDeliver,
        ];

        // 2. Production Pipeline
        // Get count grouped by status for active parts
        $pipelineStats = NpcPart::selectRaw('status, count(*) as total')
            ->whereNotIn('status', ['CLOSED'])
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
            
        $pipeline = [
            'setup' => $pipelineStats['PO_REGISTERED'] ?? 0,
            'production' => ($pipelineStats['WAITING_DEPT_CONFIRM'] ?? 0) + ($pipelineStats['IN_PRODUCTION'] ?? 0),
            'qc' => $pipelineStats['WAITING_QE_CHECK'] ?? 0,
            'management' => $pipelineStats['WAITING_MGM_CHECK'] ?? 0,
            'stock' => $pipelineStats['FINISHED'] ?? 0,
            'delivery' => $pipelineStats['OUTSTANDING'] ?? 0, // partially delivered
        ];

        // --- DASHBOARD V2 CHARTS DATA ---

        // Chart 1: 6-Month Trend (New Parts vs Finished Parts)
        $months = [];
        $newPartsData = [];
        $finishedPartsData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $months[] = $date->format('M Y');
            $newPartsData[$monthKey] = 0;
            $finishedPartsData[$monthKey] = 0;
        }

        $trendParts = NpcPart::where('created_at', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->orWhere('actual_completion_date', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->get(['created_at', 'actual_completion_date', 'status']);

        foreach ($trendParts as $tp) {
            if ($tp->created_at) {
                $mCreated = Carbon::parse($tp->created_at)->format('Y-m');
                if (isset($newPartsData[$mCreated])) {
                    $newPartsData[$mCreated]++;
                }
            }
            if ($tp->actual_completion_date && in_array($tp->status, ['FINISHED', 'CLOSED', 'OUTSTANDING'])) {
                $mFinished = Carbon::parse($tp->actual_completion_date)->format('Y-m');
                if (isset($finishedPartsData[$mFinished])) {
                    $finishedPartsData[$mFinished]++;
                }
            }
        }

        $trendChart = [
            'labels' => $months,
            'new' => array_values($newPartsData),
            'finished' => array_values($finishedPartsData),
        ];

        // Chart 2: Department Workload (Waiting Processes)
        $waitingProcesses = \App\Models\NpcPartProcess::with('department')
            ->where('status', 'WAITING')
            ->get();
            
        $deptCounts = [];
        foreach ($waitingProcesses as $proc) {
            $deptName = $proc->department ? $proc->department->name : 'Unknown';
            if (!isset($deptCounts[$deptName])) {
                $deptCounts[$deptName] = 0;
            }
            $deptCounts[$deptName]++;
        }
        
        arsort($deptCounts); // Sort high to low
        
        $departmentChart = [
            'labels' => array_keys($deptCounts),
            'data' => array_values($deptCounts)
        ];

        // Chart 3: Customer Proportion (Active Parts)
        $activeParts = NpcPart::with('purchaseOrder.event.customerCategory')
            ->whereNotIn('status', ['CLOSED', 'OUTSTANDING'])
            ->get();
            
        $custCounts = [];
        foreach ($activeParts as $pt) {
            $custName = $pt->purchaseOrder->event->customerCategory->name ?? 'No Category';
            if (!isset($custCounts[$custName])) {
                $custCounts[$custName] = 0;
            }
            $custCounts[$custName]++;
        }

        arsort($custCounts); // Sort high to low
        
        $customerChart = [
            'labels' => array_keys($custCounts),
            'data' => array_values($custCounts)
        ];

        // --- END CHARTS DATA ---

        // 3. Action Required (To-Do List)
        // a. ECN Updates
        $ecnUpdates = NpcPart::with(['product', 'purchaseOrder.event.customerCategory'])
            ->whereNotIn('status', ['FINISHED', 'CLOSED'])
            ->whereNotNull('part_revision_id')
            ->whereHas('product.docPackage', function ($q) {
                $q->whereColumn('doc_packages.current_revision_id', '!=', 'npc_parts.part_revision_id');
            })
            ->take(5)
            ->get();

        // b. Stagnant Parts (No update for > 7 days, excluding finished/closed)
        $stagnantParts = NpcPart::with(['product', 'purchaseOrder.event.customerCategory'])
            ->whereNotIn('status', ['FINISHED', 'CLOSED'])
            ->where('updated_at', '<', Carbon::now()->subDays(7))
            ->orderBy('updated_at', 'asc')
            ->take(5)
            ->get();

        // 4. Recent Deliveries / History
        $recentDeliveries = NpcPart::with(['product', 'purchaseOrder.event.customerCategory'])
            ->whereIn('status', ['CLOSED', 'OUTSTANDING'])
            ->orderBy('actual_delivery', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'metrics', 'pipeline', 'ecnUpdates', 'stagnantParts', 'recentDeliveries',
            'trendChart', 'departmentChart', 'customerChart'
        ));
    }
}
