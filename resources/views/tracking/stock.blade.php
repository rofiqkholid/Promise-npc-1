@extends('layouts.app')

@section('title', $pageTitle ?? 'Stok Barang Jadi')
@section('page_title', 'Transaksi / ' . ($pageTitle ?? 'Stok Barang Jadi (FG)'))

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid {{ $pageIcon ?? 'fa-boxes-stacked' }} text-blue-500"></i> {{ $pageTitle ?? 'Stok Barang Jadi (FG)' }}
            </h2>
            @if(isset($pageDesc))
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="p-6">
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Tujuan Kirim & Waktu</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part Info</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Qty</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status Proses</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($parts as $part)
                    @php
                        // Hitung mundur sisa hari
                        $now = \Carbon\Carbon::now()->startOfDay();
                        $target = \Carbon\Carbon::parse($part->delivery_date)->startOfDay();
                        $diffDays = $now->diffInDays($target, false);
                        
                        $isOverdue = $diffDays < 0;
                        $isUrgent = $diffDays >= 0 && $diffDays <= 3;
                        
                        $timeStatusClass = $isOverdue ? 'bg-red-100 text-red-700 border-red-200' : ($isUrgent ? 'bg-orange-100 text-orange-700 border-orange-200' : 'bg-green-100 text-green-700 border-green-200');
                        $timeStatusText = $isOverdue ? 'Terlambat ' . abs($diffDays) . ' Hari' : ($diffDays == 0 ? 'Kirim Hari Ini' : 'Sisa ' . $diffDays . ' Hari');
                        $timeStatusIcon = $isOverdue ? 'fa-triangle-exclamation' : 'fa-clock';
                        
                        // Retrieve customer info
                        $customerName = optional(optional(optional($part->product)->vehicleModel)->customer)->name ?? 'Unknown Customer';
                        $modelName = optional(optional($part->product)->vehicleModel)->name ?? '-';
                        
                        $categoryName = optional(optional(optional($part->purchaseOrder)->event)->customerCategory)->name ?? '-';
                        $grName = optional(optional(optional($part->purchaseOrder)->event)->deliveryGroup)->name ?? '-';
                    @endphp
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition text-sm {{ $part->status !== 'FINISHED' ? 'opacity-[0.65] grayscale-[0.3] pointer-events-none' : '' }}">
                        
                        {{-- Tujuan Kirim --}}
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800 dark:text-gray-100 mb-1 flex items-center gap-1.5">
                                <i class="fa-solid fa-building text-gray-400"></i> {{ $customerName }}
                            </div>
                            <div class="text-xs text-gray-500 font-medium mb-2 pl-4">
                                <div class="mb-1">Model: <span class="text-blue-600 dark:text-blue-400">{{ $modelName }}</span></div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800 rounded text-[9px] font-bold tracking-wider" title="Kategori Customer">{{ $categoryName }}</span>
                                    <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 rounded text-[9px] font-bold tracking-wider" title="Delivery Group (GR)">{{ $grName }}</span>
                                </div>
                            </div>
                            
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold border {{ $timeStatusClass }}">
                                <i class="fa-solid {{ $timeStatusIcon }}"></i> {{ $timeStatusText }}
                            </span>
                        </td>
                        
                        {{-- Part Info --}}
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-200 font-bold text-sm">{{ optional($part->product)->part_no }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($part->product)->part_name }}</div>
                            <div class="text-[10px] text-gray-400 mt-1 uppercase">PO: {{ optional($part->purchaseOrder)->po_no }}</div>
                        </td>
                        
                        {{-- Qty & Target Date --}}
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-300 font-black text-lg mb-0.5">{{ number_format($part->qty) }} <span class="text-xs font-semibold text-gray-500">PCS</span></div>
                            <div class="text-[11px] font-medium text-gray-500">
                                Target: {{ \Carbon\Carbon::parse($part->delivery_date)->format('d M Y') }}
                            </div>
                        </td>
                        
                        {{-- Approval Info --}}
                        <td class="px-6 py-4 align-top">
                            @if($part->status === 'FINISHED')
                                <div class="flex flex-col gap-1.5 mt-1">
                                    <span class="text-[11px] font-medium text-slate-600 dark:text-slate-400 flex items-center gap-1.5 line-through decoration-slate-300 opacity-60">
                                        <i class="fa-solid fa-check text-green-500"></i> Produksi Selesai
                                    </span>
                                    @if($part->qc_target_date)
                                    <span class="text-[11px] font-medium text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
                                        <i class="fa-solid fa-check-double text-emerald-500"></i> QC Passed: {{ \Carbon\Carbon::parse($part->qc_target_date)->format('d M y') }}
                                    </span>
                                    @endif
                                    @if($part->mgm_target_date)
                                    <span class="text-[11px] font-medium text-purple-700 dark:text-purple-400 flex items-center gap-1.5">
                                        <i class="fa-solid fa-check-double text-purple-500"></i> MGM Appv: {{ \Carbon\Carbon::parse($part->mgm_target_date)->format('d M y') }}
                                    </span>
                                    @endif
                                </div>
                            @else
                                <div class="mt-2 text-slate-400 text-[10px] font-medium italic">
                                    Belum selesai ({{ str_replace('_', ' ', $part->status) }})
                                </div>
                            @endif
                        </td>
                        
                        {{-- Aksi --}}
                        <td class="px-6 py-4 text-right pointer-events-auto">
                            <div class="flex flex-col items-end gap-2 text-sm">
                            @if($part->status !== 'FINISHED')
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded text-[10px] text-gray-400 italic flex items-center gap-1.5 cursor-not-allowed">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Menunggu Proses Selesai
                                </div>
                            @else
                                <form action="{{ route('tracking.deliver', $part->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow-sm font-medium transition text-xs flex items-center gap-2" onclick="return confirm('Silakan cetak Surat Jalan via sistem lain sebelum menekan tombol ini.\nLanjutkan proses pengiriman ke Customer? (Status menjadi CLOSED)');">
                                        <i class="fa-solid fa-truck-fast"></i> Tandai Telah Dikirim
                                    </button>
                                </form>
                                <p class="text-[9px] text-gray-400 mt-2 italic text-right max-w-[120px] mx-auto float-right text-balance">Ubah status menjadi selesai (Closed)</p>
                            @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-solid fa-box-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>Gudang kosong / Tidak ada part yang siap dikirim saat ini.</p>
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
@endsection

