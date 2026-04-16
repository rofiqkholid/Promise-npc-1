@extends('layouts.app')

@section('title', $pageTitle ?? 'Tracking Produksi')
@section('page_title', 'Transaksi / ' . ($pageTitle ?? 'Tracking Produksi'))

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-list-check' }} text-blue-500"></i> {{ $pageTitle ?? 'Tracking Produksi (Routing)' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    @if(isset($metrics))
    <!-- Dashboard Cards -->
    <div class="px-6 pt-6 pb-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Event -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-calendar-check mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total Event</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_events']) }}</h3>
            </div>
        </div>
        
        <!-- Card 2: Total PO -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-lg shadow-indigo-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-file-invoice mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total PO</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_pos']) }}</h3>
            </div>
        </div>

        <!-- Card 3: Total Part -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-amber-500 to-amber-600 shadow-lg shadow-amber-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-cubes mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total Part</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_parts']) }}</h3>
            </div>
        </div>

        <!-- Card 4: PO Close -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-flag-checkered mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">PO Close</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_po_close']) }}</h3>
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="p-6">
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Event</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Nomor PO</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part Info</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Qty & Target</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center" colspan="2">Progress Keseluruhan</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Durasi Sistem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($parts as $part)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group text-sm {{ $statusParam !== 'all' && $part->status !== $statusParam ? 'opacity-60 grayscale-[0.3]' : '' }}">
                        <td class="px-6 py-4">
                            <div class="text-blue-600 dark:text-blue-400 font-bold text-xs uppercase tracking-wide border border-blue-100 dark:border-blue-900 bg-blue-50 dark:bg-blue-900/30 px-2 py-1 rounded inline-block">{{ optional(optional(optional($part->purchaseOrder)->event)->masterEvent)->name ?? 'Unknown Event' }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300 font-semibold text-sm">
                            {{ optional($part->purchaseOrder)->po_no }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-200 font-medium text-sm">{{ optional($part->product)->part_no }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($part->product)->part_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-300 font-bold text-sm">{{ $part->qty }} PCS</div>
                            <div class="text-xs text-red-500 font-medium mt-1"><i class="fa-regular fa-calendar md:mr-1"></i> {{ \Carbon\Carbon::parse($part->delivery_date)->format('d M y') }}</div>
                        </td>
                        <td class="px-6 py-4 font-medium align-middle" colspan="2">
                            <div class="flex items-start justify-center w-full max-w-sm pt-2">
                                @php
                                    $phases = ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK', 'WAITING_MGM_CHECK', 'FINISHED', 'CLOSED'];
                                    $currentIndex = array_search($part->status, $phases);
                                    if($currentIndex === false) $currentIndex = -1;
                                    $steps = [
                                        ['icon' => 'fa-file-contract', 'title' => 'Draft'],
                                        ['icon' => 'fa-industry', 'title' => 'Part Making'],
                                        ['icon' => 'fa-microscope', 'title' => 'QE'],
                                        ['icon' => 'fa-user-tie', 'title' => 'MGM'],
                                        ['icon' => 'fa-boxes-stacked', 'title' => 'Stok'],
                                    ];
                                    if($part->status === 'CLOSED') $currentIndex = 5; // To fill the entire bar
                                    
                                    // Check overdue based on delivery_date
                                    $isOverdue = \Carbon\Carbon::parse($part->delivery_date)->endOfDay()->isPast() && !in_array($part->status, ['FINISHED', 'CLOSED']);
                                @endphp
                                
                                <div class="flex w-full">
                                @foreach($steps as $idx => $step)
                                    @php
                                        $isFinished = $currentIndex > $idx;
                                        $isActive = ($currentIndex === $idx);
                                        
                                        // Default (Pending)
                                        $circleClass = "text-gray-400 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800";
                                        $lineClass = "bg-gray-200 dark:bg-gray-700";
                                        $titleClass = "text-gray-400";

                                        if ($isFinished) {
                                            $circleClass = "text-white bg-emerald-500 border-emerald-500 shadow-sm";
                                            $lineClass = "bg-emerald-500";
                                            $titleClass = "text-emerald-700 dark:text-emerald-400";
                                        } elseif ($isActive) {
                                            if ($isOverdue) {
                                                $circleClass = "text-red-600 border-red-500 bg-red-50 dark:bg-red-900/30 ring-4 ring-red-100 dark:ring-red-900/40";
                                                $titleClass = "text-red-700 dark:text-red-400 font-extrabold";
                                            } else {
                                                $circleClass = "text-amber-600 border-amber-500 bg-amber-50 dark:bg-amber-900/30 ring-4 ring-amber-100 dark:ring-amber-900/40";
                                                $titleClass = "text-amber-700 dark:text-amber-400 font-extrabold";
                                            }
                                        }
                                    @endphp
                                    <div class="flex flex-col items-center flex-1 relative group">
                                        @if($idx < count($steps) - 1)
                                            <div class="absolute w-[calc(100%-1.75rem)] top-3.5 left-[calc(50%+0.875rem)] h-[3px] rounded {{ $lineClass }}"></div>
                                        @endif
                                        <div class="z-10 relative bg-white dark:bg-gray-800 {{ $circleClass }} border-2 w-7 h-7 flex items-center justify-center rounded-full text-[10px] transition-all duration-300">
                                            <i class="fa-solid {{ $step['icon'] }}"></i>
                                            
                                            @if($isFinished)
                                                <!-- Centang Overlay -->
                                                <div class="absolute -bottom-1 -right-1.5 bg-white dark:bg-gray-800 rounded-full w-3.5 h-3.5 flex items-center justify-center leading-none">
                                                    <i class="fa-solid fa-circle-check text-emerald-600 text-[12px]"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <span class="text-[9px] mt-1.5 font-bold uppercase tracking-wider text-center {{ $titleClass }}">{{ $step['title'] }}</span>
                                    </div>
                                @endforeach
                                </div>
                            </div>
                            
                            @if($part->status === 'CLOSED')
                                <div class="mt-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 text-[10px] font-bold tracking-wide shadow-sm"><i class="fa-solid fa-flag-checkered"></i> PROJECT CLOSED (TERKIRIM)</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right align-top">
                            <div class="text-[11px] font-medium text-gray-500 text-right w-full flex flex-col items-end gap-1">
                                <span class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded border border-gray-200 dark:border-gray-600">IN: {{ $part->created_at->format('d M Y') }}</span>
                                @if($part->status === 'CLOSED')
                                    <span class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 px-2 py-1 rounded border border-emerald-200 dark:border-emerald-800 shadow-sm mt-1">OUT: {{ $part->actual_delivery ? \Carbon\Carbon::parse($part->actual_delivery)->format('d M Y') : '-' }}</span>
                                @else
                                    <span class="text-amber-600 font-bold mt-1 tracking-wide"><i class="fa-solid fa-hourglass-half animate-pulse"></i> {{ str_replace(' ago', '', $part->created_at->diffForHumans()) }}</span>
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

