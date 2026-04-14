<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductionTrackingController extends Controller
{
    private function buildQuery($statusParam)
    {
        $query = \App\Models\NpcPart::with(['purchaseOrder.event.customerCategory', 'purchaseOrder.event.deliveryGroup', 'processes.process.department', 'checkpoints', 'checksheet', 'product.vehicleModel.customer'])->latest();

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
        
        return $query;
    }

    private function renderTrackingPage($statusParam, $pageTitle, $pageIcon, $pageDesc, $viewFile = 'tracking.index')
    {
        $parts = $this->buildQuery($statusParam)->paginate(15);
        return view($viewFile, compact('parts', 'statusParam', 'pageTitle', 'pageIcon', 'pageDesc'));
    }

    public function index(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('all', 'Global Tracking', 'fa-globe', 'Pantau seluruh progres PO dan Part');
    }

    public function setup(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('PO_REGISTERED', 'Setup Routing Produksi', 'fa-route', 'Menyiapkan rute dan jadwal untuk PO Baru');
    }

    public function production(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('WAITING_DEPT_CONFIRM', 'Proses Produksi', 'fa-industry', 'Pantau komponen yang sedang dalam tahap produksi');
    }

    public function qc(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('WAITING_QE_CHECK', 'Pemeriksaan Kualitas (QC)', 'fa-microscope', 'Input dan validasi pengecekan kualitas');
    }

    public function mgm(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('WAITING_MGM_CHECK', 'Persetujuan Management', 'fa-user-tie', 'Validasi dan konfirmasi final oleh manajemen');
    }

    public function stock(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('FINISHED', 'Stok Barang Jadi (FG)', 'fa-boxes-stacked', 'Komponen yang siap untuk dikirim', 'tracking.stock');
    }

    public function history(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('CLOSED', 'Riwayat Pengiriman', 'fa-truck-fast', 'Komponen yang telah terkirim ke customer');
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
