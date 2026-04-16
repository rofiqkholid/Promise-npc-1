@extends('layouts.app')

@section('title', $pageTitle ?? 'Proses Produksi')
@section('page_title', 'Transaksi / ' . ($pageTitle ?? 'Proses Produksi'))

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-industry' }} text-blue-500"></i> {{ $pageTitle ?? 'Proses Produksi' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    <!-- Table -->
    <div class="p-6">
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-64">Part Info / PO</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center w-32">Status PO</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Tinjauan Eksekusi Routing</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-48">Aksi Produksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($parts as $part)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition text-sm {{ $part->status !== 'WAITING_DEPT_CONFIRM' ? 'opacity-[0.65] grayscale-[0.3] pointer-events-none' : '' }}">
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-200 font-bold text-sm">{{ optional($part->product)->part_no }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-2">{{ optional($part->product)->part_name }}</div>
                            
                            <div class="text-blue-600 dark:text-blue-400 font-semibold text-[10px] uppercase">PO: {{ optional($part->purchaseOrder)->po_no }}</div>
                            <div class="text-[10px] text-gray-400 mt-1"><span class="font-bold text-gray-600 dark:text-gray-300">{{ number_format($part->qty) }} PCS</span> | Tgt Kirim: {{ \Carbon\Carbon::parse($part->delivery_date)->format('d M') }}</div>
                        </td>
                        <td class="px-6 py-4 text-center align-middle">
                            @if($part->status === 'PO_REGISTERED')
                                <div class="inline-flex flex-col items-center gap-1.5 px-3 py-2 rounded bg-slate-50 border border-slate-200 text-[10px] text-slate-500 italic">
                                    <i class="fa-solid fa-lock text-sm"></i> Berupa Rencana
                                </div>
                            @elseif($part->status === 'WAITING_DEPT_CONFIRM')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-yellow-100 border border-yellow-200 text-yellow-800 text-[10px] font-bold tracking-wide"><i class="fa-solid fa-gears fa-spin"></i> AKTIF DIPROSES</span>
                            @else
                                <div class="text-[10px] text-gray-400 italic font-medium"><i class="fa-solid fa-check text-green-500"></i> Diserahkan ke QC</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($part->processes->count() > 0)
                                <div class="flex flex-col gap-2">
                                    @foreach($part->processes as $process)
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full {{ $part->status !== 'PO_REGISTERED' ? 'bg-green-100 text-green-600 border border-green-200' : 'bg-gray-100 text-gray-500 border border-gray-200 shadow-inner' }} flex items-center justify-center text-[9px] font-black">
                                                {{ $process->sequence_order }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 {{ $part->status !== 'PO_REGISTERED' ? '' : 'text-gray-400' }}">{{ optional($process->process)->process_name ?? 'Unknown Process' }}</span>
                                                <span class="text-[9px] text-gray-500">{{ optional(optional($process->process)->department)->name ?? 'Departemen tidak diketahui' }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-orange-500 italic flex items-center gap-1">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Belum ada Routing
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right align-middle pointer-events-auto">
                            @if($part->status === 'PO_REGISTERED')
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Belum Masuk Produksi
                                </div>
                            @elseif($part->status === 'WAITING_DEPT_CONFIRM')
                                <button type="button"
                                    onclick="openCompleteModal({{ $part->id }}, '{{ route('tracking.status.update', $part->id) }}')"
                                    class="inline-flex px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm font-bold transition items-center gap-2 text-[11px]" style="background-color: #f59e0b;">
                                    <i class="fa-solid fa-forward-step"></i> Produksi Selesai
                                </button>
                                <p class="text-[9px] text-gray-400 mt-2 italic text-right max-w-[150px] mx-auto float-right text-balance">Klik untuk menyerahkan barang ke pemeriksaan Kualitas (QC)</p>
                            @else
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Sudah Selesai
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-solid fa-industry text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>Tidak ada operasi antrean produksi aktif saat ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($parts->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $parts->links() }}
    </div>
    @endif
</div>

{{-- Modal: Produksi Selesai --}}
<div id="modal-complete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-flag-checkered text-amber-500"></i> Konfirmasi Selesai Produksi
            </h3>
            <button onclick="closeCompleteModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl leading-none">&times;</button>
        </div>
        <form id="form-complete" method="POST">
            @csrf
            <input type="hidden" name="status" value="WAITING_QE_CHECK">
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal Selesai Aktual <span class="text-red-500">*</span></label>
                    <input type="date" name="actual_completion_date" required
                        class="w-full text-sm rounded border-gray-300 dark:border-gray-600 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-[11px] text-gray-400 mt-1 italic">Tanggal part benar-benar selesai diproduksi.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Catatan Produksi <span class="text-gray-400 text-[10px] font-normal">(opsional)</span></label>
                    <textarea name="production_notes" rows="3" placeholder="Misal: selesai lebih awal / ada kendala mesin..."
                        class="w-full text-sm rounded border-gray-300 dark:border-gray-600 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-700 dark:text-white"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 rounded-b-xl">
                <button type="button" onclick="closeCompleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-lg shadow-sm transition flex items-center gap-1">
                    <i class="fa-solid fa-check"></i> Konfirmasi Serah Terima
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openCompleteModal(partId, actionUrl) {
    document.getElementById('form-complete').action = actionUrl;
    document.getElementById('modal-complete').classList.remove('hidden');
    // Set today as default
    const dateInput = document.querySelector('#modal-complete input[name="actual_completion_date"]');
    if (!dateInput.value) dateInput.value = new Date().toISOString().substring(0, 10);
}
function closeCompleteModal() {
    document.getElementById('modal-complete').classList.add('hidden');
}
// Close on backdrop click
document.getElementById('modal-complete').addEventListener('click', function(e) {
    if (e.target === this) closeCompleteModal();
});
</script>
@endpush

