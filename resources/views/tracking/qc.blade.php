@extends('layouts.app')

@section('title', $pageTitle ?? 'Pemeriksaan Kualitas (QC)')
@section('page_title', 'Transaksi / ' . ($pageTitle ?? 'Pemeriksaan Kualitas (QC)'))

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-microscope' }} text-blue-500"></i> {{ $pageTitle ?? 'Pemeriksaan Kualitas (QC)' }}
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
                        <th scope="col" class="px-6 py-4 font-semibold w-72">Identitas Produk</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Serah Terima Produksi</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status Pengecekan</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-48">Aksi Quality</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($parts as $part)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition text-sm {{ $part->status !== 'WAITING_QE_CHECK' ? 'opacity-[0.65] grayscale-[0.3] pointer-events-none' : '' }}">
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-200 font-bold text-sm">{{ optional($part->product)->part_no }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1.5">{{ optional($part->product)->part_name }}</div>
                            <div class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50 dark:bg-gray-700 px-2 py-0.5 inline-block rounded border border-gray-200 dark:border-gray-600">{{ optional($part->product->vehicleModel)->name ?? 'Unknown Model' }}</div>
                            <div class="text-gray-800 dark:text-gray-300 font-black flex items-center gap-1.5 mt-2"><i class="fa-solid fa-boxes-stacked text-gray-400"></i> {{ number_format($part->qty) }} <span class="text-xs font-semibold text-gray-500">PCS</span></div>
                        </td>
                        <td class="px-6 py-4 text-center align-middle">
                            @if(in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM']))
                                <div class="inline-flex flex-col items-center gap-1.5 px-3 py-2 rounded bg-slate-50 border border-slate-200 text-[10px] text-slate-500 italic">
                                    <i class="fa-solid fa-industry text-sm"></i> Sedang Diproduksi
                                </div>
                            @else
                                <div class="flex flex-col items-center gap-1">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-green-50 border border-green-200 text-green-700 text-[10px] font-bold"><i class="fa-solid fa-check-double"></i> Produksi Selesai</span>
                                    <span class="text-[11px] text-gray-500 font-medium">Tgl: {{ $part->actual_completion_date ? \Carbon\Carbon::parse($part->actual_completion_date)->format('d M y') : '-' }}</span>
                                    @if($part->production_notes)
                                    <span class="text-[9px] text-gray-400 italic max-w-[150px] truncate" title="{{ $part->production_notes }}">"{{ $part->production_notes }}"</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center align-middle">
                            @if(in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM']))
                                <div class="text-[10px] text-gray-400 italic font-medium">Menunggu Barang Masuk</div>
                            @elseif($part->status === 'WAITING_QE_CHECK')
                                @php
                                    $hasChecksheet = $part->checksheet()->exists();
                                @endphp
                                @if($hasChecksheet)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-100 border border-blue-200 text-blue-800 text-[10px] font-bold shadow-sm"><i class="fa-solid fa-pen-to-square"></i> DIISI & SEDANG DICEK</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-orange-100 border border-orange-200 text-orange-800 text-[10px] font-bold shadow-sm"><i class="fa-solid fa-triangle-exclamation animate-pulse"></i> BELUM DIINPUT QC</span>
                                @endif
                                <div class="mt-2 text-[10px] text-gray-500"><i class="fa-solid fa-calendar-check text-gray-400 mr-1"></i> Target QC: <span class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($part->qc_target_date)->format('d M') }}</span></div>
                            @else
                                <div class="text-[10px] text-emerald-600 font-bold bg-emerald-50 border border-emerald-100 px-2 py-1 rounded inline-flex items-center gap-1"><i class="fa-solid fa-shield-halved"></i> LULUS QC</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right align-middle pointer-events-auto">
                            @if(in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM']))
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Belum Masuk QC
                                </div>
                            @elseif($part->status === 'WAITING_QE_CHECK')
                                <a href="{{ route('checksheets.create', $part->id) }}" class="inline-flex px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded shadow-sm font-bold transition items-center gap-2 text-[11px]" style="background-color: #f97316;">
                                    <i class="fa-regular fa-clipboard"></i> Input Quality (QC)
                                </a>
                                <p class="text-[9px] text-gray-400 mt-2 italic text-right max-w-[150px] mx-auto float-right text-balance">Isi formulir parameter kualitas & loloskan ke MGM</p>
                            @else
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Sudah Diperiksa
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-solid fa-microscope text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>Tidak ada barang yang masuk antrean pengecekan kualitas saat ini.</p>
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

