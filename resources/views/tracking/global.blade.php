@extends('layouts.app')

@section('title', $pageTitle ?? 'Global Tracking')
@section('page_title', 'Transaksi / ' . ($pageTitle ?? 'Global Tracking'))

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-list-check' }} text-blue-500"></i> {{ $pageTitle ?? 'Global Tracking' }}
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

    <!-- Table & Modals -->
    <div class="p-6" x-data="{ activeModal: null }">
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-1/4">Event & Nomor PO</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-1/12 text-center">Part Count</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-1/12">Terdekat</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center w-5/12">Progres Keseluruhan</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-1/6">Durasi Sistem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($pos as $po)
                        @php
                            $poParts = $po->parts;
                            $totalParts = $poParts->count();
                            
                            $phases = ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK', 'WAITING_MGM_CHECK', 'FINISHED', 'CLOSED'];
                            $steps = [
                                ['icon' => 'fa-file-contract', 'title' => 'Draft'],
                                ['icon' => 'fa-industry', 'title' => 'Part Making'],
                                ['icon' => 'fa-microscope', 'title' => 'QE'],
                                ['icon' => 'fa-user-tie', 'title' => 'MGM'],
                                ['icon' => 'fa-boxes-stacked', 'title' => 'Stok'],
                            ];

                            $reachedCounts = [];
                            $passedCounts = [];
                            $isOverdueAny = false;
                            
                            foreach($steps as $idx => $step) {
                                $rCount = 0;
                                $pCount = 0;
                                foreach($poParts as $p) {
                                    $pIndex = array_search($p->status, $phases);
                                    if ($pIndex === false) $pIndex = -1;
                                    if ($p->status === 'CLOSED') $pIndex = 5;
                                    
                                    if ($pIndex >= $idx) $rCount++;
                                    if ($pIndex > $idx || ($idx == 4 && in_array($p->status, ['FINISHED', 'CLOSED']))) {
                                        $pCount++;
                                    }
                                    
                                    if (!in_array($p->status, ['FINISHED', 'CLOSED']) && \Carbon\Carbon::parse($p->delivery_date)->endOfDay()->isPast()) {
                                        $isOverdueAny = true;
                                    }
                                }
                                $reachedCounts[$idx] = $rCount;
                                $passedCounts[$idx] = $pCount;
                            }
                            
                            $earliestDelivery = $poParts->min('delivery_date');
                        @endphp
                        
                        <tr @click="activeModal = {{ $po->id }}" class="cursor-pointer bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition group text-sm">
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-4">
                                    <div class="mt-1 w-6 h-6 rounded bg-blue-100 text-blue-500 dark:bg-blue-900/50 dark:text-blue-400 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-expand text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-blue-600 dark:text-blue-400 font-bold text-[11px] uppercase tracking-wide bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 px-2 py-0.5 inline-block rounded mb-1">{{ optional(optional($po->event)->masterEvent)->name ?? 'Unknown Event' }}</div>
                                        <div class="text-gray-800 dark:text-gray-200 font-bold text-sm">{{ $po->po_no }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 px-3 py-1 rounded-full font-bold text-xs">{{ $totalParts }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($earliestDelivery)
                                    <div class="text-xs {{ \Carbon\Carbon::parse($earliestDelivery)->endOfDay()->isPast() && (!isset($reachedCounts[4]) || $reachedCounts[4] !== $totalParts) ? 'text-red-500 font-bold' : 'text-gray-600 font-medium' }}"><i class="fa-regular fa-calendar-alt md:mr-1"></i> {{ \Carbon\Carbon::parse($earliestDelivery)->format('d M y') }}</div>
                                @else
                                    - 
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium align-middle">
                                <div class="flex w-full items-start justify-center min-w-[300px]">
                                    @foreach($steps as $idx => $step)
                                        @php
                                            $rCount = $reachedCounts[$idx];
                                            $pCount = $passedCounts[$idx];
                                            $pPct = $totalParts > 0 ? round(($pCount / $totalParts) * 100) : 0;
                                            
                                            $rCountNext = isset($reachedCounts[$idx+1]) ? $reachedCounts[$idx+1] : 0;
                                            $rPctNext = $totalParts > 0 ? round(($rCountNext / $totalParts) * 100) : 0;

                                            $lineBg = "bg-gray-200 dark:bg-gray-700";
                                            if ($rPctNext == 100) $lineBg = "bg-emerald-500";
                                            
                                            if ($pPct == 100) {
                                                $circleBorder = "border-emerald-500";
                                                $fillClass = "bg-emerald-500";
                                                $iconColor = "text-white";
                                                $titleClass = "text-emerald-700 dark:text-emerald-400";
                                                $showCheck = true;
                                            } else if ($rCount > 0) {
                                                // Either active or partially passed
                                                $circleBorder = "border-amber-500 ring-2 ring-amber-100";
                                                if ($isOverdueAny && $pPct < 100) {
                                                    $circleBorder = "border-red-500 ring-2 ring-red-100";
                                                }
                                                $fillClass = ($isOverdueAny && $pPct < 100) ? "bg-red-400" : "bg-amber-400";
                                                $iconColor = $pPct > 50 ? "text-white" : (($isOverdueAny && $pPct < 100) ? "text-red-700" : "text-amber-700");
                                                $titleClass = ($isOverdueAny && $pPct < 100) ? "text-red-600 font-extrabold" : "text-amber-600 font-extrabold";
                                                $showCheck = false;
                                            } else {
                                                $circleBorder = "border-gray-200 dark:border-gray-700";
                                                $fillClass = "bg-transparent";
                                                $iconColor = "text-gray-400";
                                                $titleClass = "text-gray-400";
                                                $showCheck = false;
                                            }
                                        @endphp
                                        <div class="flex flex-col items-center flex-1 relative group">
                                            @if($idx < count($steps) - 1)
                                                <div class="absolute w-[calc(100%-2.25rem)] top-[14px] left-[calc(50%+1.125rem)] h-[3px] rounded {{ $lineBg }} overflow-hidden">
                                                    @if($rPctNext > 0 && $rPctNext < 100)
                                                        <div class="h-full bg-emerald-500 rounded transition-all duration-700" style="width: {{ $rPctNext }}%"></div>
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            <div class="z-10 relative bg-white dark:bg-gray-800 border-2 {{ $circleBorder }} w-8 h-8 flex items-center justify-center rounded-full text-[12px] overflow-hidden shadow-sm transition-all duration-300" title="{{ $pCount }} dari {{ $totalParts }} Parts selesai ({{ $pPct }}%)">
                                                <div class="absolute bottom-0 left-0 right-0 {{ $fillClass }} transition-all duration-700 ease-out opacity-90" style="height: {{ $pPct }}%; z-index:0;"></div>
                                                <i class="fa-solid {{ $step['icon'] }} relative z-10 {{ $iconColor }}"></i>
                                                
                                                @if($showCheck)
                                                    <div class="absolute -bottom-1 -right-1.5 bg-white dark:bg-gray-800 rounded-full w-4 h-4 flex items-center justify-center z-30">
                                                        <i class="fa-solid fa-circle-check text-emerald-600 text-[12px]"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="flex flex-col items-center mt-2 h-8">
                                                <span class="text-[9px] font-bold uppercase tracking-wider text-center {{ $titleClass }} leading-tight block">{{ $step['title'] }}</span>
                                                @if($rCount > 0 && $pPct < 100)
                                                    <span class="text-[9px] font-black {{ $isOverdueAny ? 'text-red-600' : 'text-amber-600' }} mt-0.5">{{ $pPct }}%</span>
                                                @elseif($pPct == 100)
                                                    <span class="text-[9px] font-black text-emerald-600 mt-0.5"><i class="fa-solid fa-check"></i></span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right align-top">
                                <div class="text-[11px] font-medium text-gray-500 text-right w-full flex flex-col items-end gap-1">
                                    <span class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded border border-gray-200 dark:border-gray-600">IN: {{ $po->created_at->format('d M y') }}</span>
                                    @if(isset($reachedCounts[4]) && $reachedCounts[4] === $totalParts && $totalParts > 0)
                                        <span class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 px-2 py-1 rounded border border-emerald-200 shadow-sm mt-1 font-bold"><i class="fa-solid fa-check-double"></i> COMPLETE</span>
                                    @else
                                        <span class="text-amber-600 font-bold mt-1 tracking-wide"><i class="fa-solid fa-spinner fa-spin"></i> ACTIVE</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                    <p>Tidak ada data PO / rute aktif.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Render All Modals for details -->
        @foreach($pos as $po)
            <div x-show="activeModal === {{ $po->id }}" class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak style="display: none;">
                <!-- Backdrop -->
                <div x-show="activeModal === {{ $po->id }}"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
              
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div x-show="activeModal === {{ $po->id }}"
                            @click.away="activeModal = null"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-5xl">
                            
                            <!-- Header -->
                            <div class="bg-gray-50/80 dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2" id="modal-title">
                                    <i class="fa-solid fa-list-check text-blue-500"></i> Rincian Part: {{ $po->po_no }}
                                </h3>
                                <button type="button" @click="activeModal = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>
                            
                            <!-- Body table -->
                            <div class="px-6 py-4 overflow-y-auto max-h-[70vh]">
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    <table class="w-full text-[11px] text-left text-slate-600 dark:text-slate-400">
                                        <thead class="bg-blue-50/50 dark:bg-blue-900/20 text-slate-700 dark:text-slate-300 border-b border-gray-200 dark:border-gray-700 uppercase tracking-wider">
                                            <tr>
                                                <th class="px-4 py-3 w-1/4">Part Details</th>
                                                <th class="px-4 py-3 w-1/5">Qty & Target</th>
                                                <th class="px-4 py-3">Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800" x-data="{ expandedPM: null }">
                                            @foreach($po->parts as $part)
                                                <tr class="hover:bg-blue-50/30 transition">
                                                    <td class="px-4 py-3">
                                                        <div class="font-bold text-gray-800 dark:text-gray-200 text-xs">{{ optional($part->product)->part_no }}</div>
                                                        <div class="text-[10px] text-gray-500 mt-0.5">{{ optional($part->product)->part_name }}</div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="font-bold text-gray-700">{{ $part->qty }} PCS</div>
                                                        <div class="text-[10px] {{ \Carbon\Carbon::parse($part->delivery_date)->endOfDay()->isPast() && !in_array($part->status, ['FINISHED', 'CLOSED']) ? 'text-red-500 font-bold' : 'text-gray-500' }} mt-0.5">
                                                            <i class="fa-regular fa-calendar md:mr-1"></i> {{ \Carbon\Carbon::parse($part->delivery_date)->format('d M Y') }}
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 align-middle">
                                                        @php
                                                            // Steps definition for modal
                                                            $phases = ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK', 'WAITING_MGM_CHECK', 'FINISHED', 'CLOSED'];
                                                            $stepsArr = [
                                                                ['icon' => 'fa-file-contract', 'title' => 'Draft'],
                                                                ['icon' => 'fa-industry', 'title' => 'Part Making'],
                                                                ['icon' => 'fa-microscope', 'title' => 'QE'],
                                                                ['icon' => 'fa-user-tie', 'title' => 'MGM'],
                                                                ['icon' => 'fa-boxes-stacked', 'title' => 'Stok'],
                                                            ];
                                            
                                                            $pIndex = array_search($part->status, $phases);
                                                            if ($pIndex === false) $pIndex = -1;
                                                            if ($part->status === 'CLOSED') $pIndex = 5;
                                                            $pOverdue = \Carbon\Carbon::parse($part->delivery_date)->endOfDay()->isPast() && !in_array($part->status, ['FINISHED', 'CLOSED']);
                                                        @endphp
                                                        <div class="flex items-start w-full min-w-[200px] pt-1">
                                                            @foreach($stepsArr as $sIdx => $stepObj)
                                                                @php
                                                                    $isReached = $pIndex >= $sIdx;
                                                                    $isActive = $pIndex == $sIdx;
                                                                    $isPast = $pIndex > $sIdx;
                                                                    
                                                                    $lineBg = "bg-gray-200 dark:bg-gray-700";
                                                                    if ($isPast) {
                                                                        $lineBg = "bg-emerald-500";
                                                                    }
                                                                    
                                                                    if ($isPast || ($isReached && $sIdx == 4 && in_array($part->status, ['FINISHED', 'CLOSED']))) {
                                                                        $circleBorder = "border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600";
                                                                        if ($isActive) $circleBorder .= " ring-2 ring-emerald-100";
                                                                    } else if ($isActive) {
                                                                        if ($pOverdue) {
                                                                            $circleBorder = "border-red-500 bg-red-50 dark:bg-red-900/30 text-red-600 ring-2 ring-red-100";
                                                                        } else {
                                                                            $circleBorder = "border-amber-500 bg-amber-50 dark:bg-amber-900/30 text-amber-600 ring-2 ring-amber-100";
                                                                        }
                                                                    } else {
                                                                        $circleBorder = "border-gray-200 dark:bg-gray-800 text-gray-400";
                                                                    }
                                                                @endphp
                                                                <div class="flex flex-col items-center flex-1 relative">
                                                                    @if($sIdx < count($stepsArr) - 1)
                                                                        <div class="absolute w-[calc(100%-1.25rem)] top-[10px] left-[calc(50%+0.625rem)] h-[2px] rounded {{ $lineBg }}"></div>
                                                                    @endif
                                                                    
                                                                    @if($stepObj['title'] === 'Part Making')
                                                                        <div @click="expandedPM = expandedPM === {{ $part->id }} ? null : {{ $part->id }}" class="z-10 relative border-2 {{ $circleBorder }} w-5 h-5 flex items-center justify-center rounded-full text-[8px] transition-all duration-300 bg-white cursor-pointer hover:scale-125 hover:shadow-md" title="Klik untuk melihat Detail Rute Part Making">
                                                                            <i class="fa-solid {{ $stepObj['icon'] }}"></i>
                                                                            @if($isPast || ($isReached && $sIdx == 4 && in_array($part->status, ['FINISHED', 'CLOSED'])))
                                                                                <div class="absolute -bottom-1 -right-1 bg-white rounded-full w-2.5 h-2.5 flex items-center justify-center text-[7px] text-emerald-600">
                                                                                    <i class="fa-solid fa-circle-check"></i>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @else
                                                                        <div class="z-10 relative border-2 {{ $circleBorder }} w-5 h-5 flex items-center justify-center rounded-full text-[8px] transition-all duration-300 bg-white">
                                                                            <i class="fa-solid {{ $stepObj['icon'] }}"></i>
                                                                            @if($isPast || ($isReached && $sIdx == 4 && in_array($part->status, ['FINISHED', 'CLOSED'])))
                                                                                <div class="absolute -bottom-1 -right-1 bg-white rounded-full w-2.5 h-2.5 flex items-center justify-center text-[7px] text-emerald-600">
                                                                                    <i class="fa-solid fa-circle-check"></i>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    <span class="text-[7px] font-bold uppercase tracking-wider text-center mt-1 {{ $isActive ? ($pOverdue ? 'text-red-600' : 'text-amber-600') : ($isReached ? 'text-emerald-600' : 'text-gray-400') }}">{{ $stepObj['title'] }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Sub Processes Expandable Row for Part Making -->
                                                <tr x-show="expandedPM === {{ $part->id }}" class="bg-blue-50/20 dark:bg-gray-800/30 transition-all" x-cloak style="display:none;">
                                                    <td colspan="3" class="px-4 py-3 border-l-4 border-blue-400">
                                                        <div class="ml-2">
                                                            <h5 class="text-[9px] font-bold uppercase tracking-widest text-slate-500 mb-2 flex items-center gap-1.5"><i class="fa-solid fa-route"></i> Rute Detail: Part Making</h5>
                                                            @if($part->processes && $part->processes->count() > 0)
                                                                <div class="flex flex-wrap items-center gap-x-1 gap-y-2 mt-1 relative z-10 w-full overflow-x-auto pb-1">
                                                                    @foreach($part->processes->sortBy('sequence_order')->values() as $pIdx => $pProc)
                                                                        @php
                                                                            $spStatus = $pProc->status; 
                                                                            $spIcon = "fa-cogs";
                                                                            if ($spStatus === 'FINISHED' || $pProc->actual_completion_date) {
                                                                                $spBg = "bg-emerald-100 text-emerald-700 border-emerald-300";
                                                                                $spIcon = "fa-check";
                                                                            } elseif (in_array($spStatus, ['ON_PROGRESS', 'IN_PROGRESS', 'IN_PRODUCTION'])) {
                                                                                $spBg = "bg-amber-100/80 text-amber-700 border-amber-300 ring-1 ring-amber-300";
                                                                                $spIcon = "fa-spinner fa-spin";
                                                                            } else {
                                                                                $spBg = "bg-gray-100 text-gray-500 border-gray-200";
                                                                                $spIcon = "fa-clock";
                                                                            }
                                                                        @endphp
                                                                        <div class="flex items-center shrink-0">
                                                                            <div class="flex items-center gap-1.5 px-2 py-1 rounded text-[9px] font-bold border {{ $spBg }} shadow-sm">
                                                                                <i class="fa-solid {{ $spIcon }}"></i>
                                                                                {{ optional($pProc->process)->process_name ?? 'Proses ' . ($pIdx+1) }}
                                                                            </div>
                                                                            @if($pIdx < $part->processes->count() - 1)
                                                                                <div class="w-3 h-px bg-gray-300 mx-1"></div>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <div class="text-[10px] text-gray-400 italic bg-gray-100/50 p-2 rounded w-max">Belum ada pemetaan rute (Routing) untuk part ini.</div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Footer -->
                            <div class="bg-gray-50 dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-row-reverse">
                                <button type="button" @click="activeModal = null" class="inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    @if($pos->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $pos->links() }}
    </div>
    @endif
</div>
@endsection

