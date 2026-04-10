@extends('layouts.app')

@section('title', 'Tracking Produksi')
@section('page_title', 'Transaksi / Tracking Produksi')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-list-check text-blue-500"></i> Tracking Produksi (Routing)
        </h2>
    </div>

    <!-- Tabs -->
    <div class="px-6 border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
            @php
                $tabs = [
                    'all' => 'Semua',
                    'PO_REGISTERED' => 'Baru (Draft PO)',
                    'WAITING_DEPT_CONFIRM' => 'Proses Produksi',
                    'WAITING_QE_CHECK' => 'Waiting QC',
                    'WAITING_MGM_CHECK' => 'Waiting MGM',
                    'FINISHED' => 'FG Stock',
                    'CLOSED' => 'Terkirim (Closed)'
                ];
            @endphp
            
            @foreach($tabs as $key => $label)
                <a href="{{ route('tracking.index', ['status' => $key]) }}"
                   class="{{ $statusParam === $key ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-gray-300 dark:text-slate-400 dark:hover:text-slate-300' }}
                          whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition transition-colors">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mx-6 mt-4 rounded">
        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Table -->
    <div class="p-6">
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Event / PO</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part Info</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Qty & Target</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-64">Antrean Proses (Routing)</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-40">Status Global</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($parts as $part)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group text-sm {{ $statusParam !== 'all' && $part->status !== $statusParam ? 'opacity-60 grayscale-[0.3]' : '' }}">
                        <td class="px-6 py-4">
                            <div class="text-blue-600 dark:text-blue-400 font-semibold text-sm">{{ $part->po_no }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ optional($part->npcEvent)->event_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-200 font-medium text-sm">{{ $part->part_no }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ $part->part_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-300 font-bold text-sm">{{ $part->qty }} PCS</div>
                            <div class="text-xs text-red-500 font-medium mt-1"><i class="fa-regular fa-calendar md:mr-1"></i> {{ \Carbon\Carbon::parse($part->delivery_date)->format('d M y') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($part->processes->count() > 0)
                                {{-- Routing chips: horizontal, wrap --}}
                                <div class="flex flex-wrap gap-1 mb-1.5">
                                    @foreach($part->processes as $process)
                                        <span class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-[10px] font-semibold px-1.5 py-0.5 rounded" title="{{ $process->department }}">
                                            <span class="text-gray-400 font-bold">{{ $process->sequence_order }}.</span>
                                            {{ $process->process_name }}
                                        </span>
                                    @endforeach
                                </div>
                                {{-- QC & MGM targets inline --}}
                                <div class="flex flex-wrap gap-x-3 gap-y-0.5">
                                    @if($part->qc_target_date)
                                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
                                        <i class="fa-solid fa-microscope"></i> QE: {{ \Carbon\Carbon::parse($part->qc_target_date)->format('d M y') }}
                                    </span>
                                    @endif
                                    @if($part->mgm_target_date)
                                    <span class="text-[10px] text-purple-600 dark:text-purple-400 font-medium flex items-center gap-1">
                                        <i class="fa-solid fa-user-check"></i> MGM: {{ \Carbon\Carbon::parse($part->mgm_target_date)->format('d M y') }}
                                    </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-orange-500 italic flex items-center gap-1">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Belum ada Routing
                                </span>
                            @endif

                        </td>
                        <td class="px-6 py-4 font-medium align-top">
                            @if($part->status === 'PO_REGISTERED')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 text-xs font-medium"><i class="fa-solid fa-file-contract"></i> Draft PO</span>
                            @elseif($part->status === 'WAITING_DEPT_CONFIRM')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-medium"><i class="fa-solid fa-industry"></i> In Production</span>
                            @elseif($part->status === 'WAITING_QE_CHECK')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-orange-100 text-orange-800 text-xs font-medium"><i class="fa-solid fa-magnifying-glass"></i> QE Check</span>
                            @elseif($part->status === 'WAITING_MGM_CHECK')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 text-xs font-medium"><i class="fa-solid fa-user-tie"></i> MGM Check</span>
                            @elseif($part->status === 'FINISHED')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium"><i class="fa-solid fa-boxes-stacked"></i> FG Stock</span>
                            @elseif($part->status === 'CLOSED')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-medium"><i class="fa-solid fa-truck-fast"></i> Terkirim</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-100 text-gray-800 text-xs font-medium">{{ $part->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right align-top">
                            <div class="flex flex-col items-end gap-2 text-sm">
                            @if($statusParam !== 'all' && $part->status !== $statusParam)
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded text-[10px] text-gray-400 italic flex items-center gap-1.5 cursor-not-allowed">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Menunggu Proses Sebelumnya
                                </div>
                            @elseif($part->status === 'PO_REGISTERED')
                                <a href="{{ route('parts.routing.edit', $part->id) }}" class="inline-flex px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow-sm font-medium transition items-center gap-1 text-xs" style="background-color: #4f46e5;">
                                    <i class="fa-solid fa-route"></i> Set Routing Schedule
                                </a>
                            @elseif($part->status === 'WAITING_DEPT_CONFIRM')
                                <button type="button"
                                    onclick="openCompleteModal({{ $part->id }}, '{{ route('tracking.status.update', $part->id) }}')"
                                    class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded shadow-sm font-medium transition text-xs" style="background-color: #f59e0b;">
                                    <i class="fa-solid fa-forward-step mr-1"></i> Produksi Selesai
                                </button>
                            @elseif($part->status === 'WAITING_QE_CHECK')
                                <a href="{{ route('checksheets.create', $part->id) }}" class="inline-flex px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded shadow-sm font-medium transition items-center gap-1 text-xs" style="background-color: #f97316;">
                                    <i class="fa-regular fa-clipboard"></i> Input Quality (QC)
                                </a>
                            @elseif($part->status === 'WAITING_MGM_CHECK')
                                <a href="{{ route('checksheets.create', $part->id) }}" class="inline-flex px-3 py-1.5 bg-purple-500 hover:bg-purple-600 text-white rounded shadow-sm font-medium transition items-center gap-1 text-xs" style="background-color: #a855f7;">
                                    <i class="fa-solid fa-check-double"></i> MGM Approve
                                </a>
                            @elseif($part->status === 'FINISHED')
                                <form action="{{ route('tracking.deliver', $part->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded shadow-sm font-medium transition text-xs" style="background-color: #2563eb;" onclick="return confirm('Kirim part ini ke Customer? (Status menjadi CLOSED)');">
                                        <i class="fa-solid fa-truck-fast mr-1"></i> Proses Pengiriman
                                    </button>
                                </form>
                            @elseif($part->status === 'CLOSED')
                                <span class="text-blue-600 border border-blue-200 bg-blue-50 px-3 py-1 rounded text-xs font-semibold cursor-default">
                                    <i class="fa-solid fa-check"></i> Selesai
                                </span>
                            @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>Tidak ada data rute / tracking pada status ini.</p>
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
                    <i class="fa-solid fa-check"></i> Konfirmasi Selesai
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
