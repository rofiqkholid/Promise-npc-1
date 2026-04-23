<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductionTrackingController extends Controller
{
    private function buildQuery($statusParam)
    {
        $query = \App\Models\NpcPart::with(['purchaseOrder.event.customerCategory', 'purchaseOrder.event.deliveryGroup', 'processes.process', 'processes.department', 'checkpoints', 'checksheet', 'product.vehicleModel.customer'])->latest();

        if ($statusParam !== 'all') {
            if ($statusParam === 'CLOSED') {
                // Halaman History khusus untuk yang sudah CLOSED atau OUTSTANDING (pengiriman parsial)
                $query->whereIn('status', ['CLOSED', 'OUTSTANDING']);
            } else {
                // Tampilkan SEMUA task (termasuk yang sudah CLOSED) agar riwayat tidak hilang dari Stock/tahap lain
            }
        }
        
        if (request()->has('filter') && request('filter') === 'ecn_updated') {
            $query->whereNotIn('status', ['FINISHED', 'CLOSED'])
                  ->whereNotNull('part_revision_id')
                  ->whereHas('product.docPackage', function ($q) {
                      $q->whereColumn('doc_packages.current_revision_id', '!=', 'npc_parts.part_revision_id');
                  });
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
        $metrics = [
            'total_events' => \App\Models\NpcEvent::count(),
            'total_pos' => \App\Models\NpcPurchaseOrder::count(),
            'total_parts' => \App\Models\NpcPart::count(),
            'total_po_close' => \App\Models\NpcPart::where('status', 'CLOSED')->count(),
        ];

        $query = \App\Models\NpcPurchaseOrder::with(['event', 'parts.product'])
                ->whereHas('parts');
        
        $pos = $query->latest()->paginate(10);

        return view('tracking.global', [
            'pos' => $pos,
            'statusParam' => 'all',
            'pageTitle' => 'Global Tracking',
            'pageIcon' => 'fa-globe',
            'pageDesc' => 'Pantau progres berdasarkan Purchase Order (PO)',
            'metrics' => $metrics
        ]);
    }

    public function setup(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('PO_REGISTERED', 'Setup Routing Produksi', 'fa-route', 'Menyiapkan rute dan jadwal untuk PO Baru', 'tracking.setup');
    }

    public function production(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('WAITING_DEPT_CONFIRM', 'Proses Produksi', 'fa-industry', 'Pantau komponen yang sedang dalam tahap produksi', 'tracking.production');
    }

    public function qc(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('WAITING_QE_CHECK', 'Pemeriksaan Kualitas (QC)', 'fa-microscope', 'Input dan validasi pengecekan kualitas', 'tracking.qc');
    }

    public function mgm(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('WAITING_MGM_CHECK', 'Persetujuan Management', 'fa-user-tie', 'Validasi dan konfirmasi final oleh manajemen', 'tracking.mgm');
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
            'status' => 'required|in:PO_REGISTERED,WAITING_DEPT_CONFIRM,IN_PRODUCTION,WAITING_QE_CHECK,WAITING_MGM_CHECK,FINISHED,OUTSTANDING,CLOSED',
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

    public function completeProcess(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        $request->validate([
            'process_id' => 'required|exists:npc_part_processes,id',
            'actual_completion_date' => 'required|date',
            'actual_qty' => 'required|integer|min:0',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'production_notes' => 'nullable|string|max:500',
        ]);

        $process = \App\Models\NpcPartProcess::where('id', $request->process_id)
            ->where('npc_part_id', $part->id)
            ->firstOrFail();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('production_proofs', 'public');
        }

        // Tandai proses ini selesai
        $process->update([
            'status' => 'FINISHED',
            'actual_completion_date' => $request->actual_completion_date,
            'actual_qty' => $request->actual_qty,
            'photo_proof' => $photoPath
        ]);

        // Cek apakah part ini masih punya proses yang belum selesai berdasar urutan
        $remainingProcesses = \App\Models\NpcPartProcess::where('npc_part_id', $part->id)
            ->where('status', 'WAITING')
            ->count();

        // Jika tidak ada sisa proses, barulah lempar part ke divisi QC
        if ($remainingProcesses === 0) {
            $part->update([
                'status' => 'WAITING_QE_CHECK',
                'actual_completion_date' => $request->actual_completion_date,
                'production_notes' => $request->production_notes,
            ]);
            return back()->with('success', 'Rangkaian Produksi tamat. Barang berhasil diserahkan ke QC!');
        }

        return back()->with('success', 'Proses selesai! Berlajut ke departemen berikutnya.');
    }

    public function deliver(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        $request->validate([
            'delivered_qty' => 'required|integer|min:1'
        ]);

        $maxQty = $part->qty - $part->delivered_qty;
        if ($request->delivered_qty > $maxQty) {
            return back()->with('error', 'Jumlah yang dikirim melebihi sisa barang (' . $maxQty . ' PCS).');
        }

        $newDeliveredQty = $part->delivered_qty + $request->delivered_qty;
        $status = ($newDeliveredQty >= $part->qty) ? 'CLOSED' : 'OUTSTANDING';

        $part->update([
            'status' => $status,
            'delivered_qty' => $newDeliveredQty,
            'actual_delivery' => \Carbon\Carbon::now()
        ]);

        $msg = ($status === 'CLOSED') 
            ? 'Part berhasil dikirim seluruhnya ke customer dan ditutup.'
            : 'Pengiriman parsial berhasil dicatat (' . $request->delivered_qty . ' PCS). Sisa barang masih outstanding.';

        return back()->with('success', $msg);
    }
}
