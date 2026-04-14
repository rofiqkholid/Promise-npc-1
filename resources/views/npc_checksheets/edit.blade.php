@extends('layouts.app')

@section('title', 'Input Quality Checksheet')
@section('page_title', 'Checksheet Produksi / ' . (optional($part->purchaseOrder)->po_no ?? 'Part Telah Dihapus'))

@section('content')
@php
    $isMGM = $part ? $part->status === 'WAITING_MGM_CHECK' : false;
    $role = $isMGM ? 'MGM' : 'QC';
    $readonly = false; // Based on new flow, MGM checks the points, QC just uploads file
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-5xl mx-auto">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                <i class="fa-solid fa-clipboard-check text-blue-500 mr-2"></i> PART EVENT DELIVERY CHECKSHEET
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                <strong>Part No:</strong> {{ optional($part->product)->part_no ?? 'N/A' }} | <strong>Customer:</strong> {{ optional(optional($part)->event)->customer->customer_name ?? 'N/A' }}
            </p>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full {{ $isMGM ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800' }} text-sm font-semibold shadow-sm border {{ $isMGM ? 'border-purple-200' : 'border-orange-200' }}">
                <i class="fa-solid fa-user-shield"></i> {{ $role }} Review Mode
            </span>
        </div>
    </div>

    <!-- Part Context Info -->
    <div class="px-6 py-4 grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Event/Project</span>
            <span class="text-sm font-medium text-gray-700 dark:text-white">{{ optional($part->event)->event_name }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Qty Output</span>
            <span class="text-sm font-bold text-gray-700 dark:text-white">{{ $part->qty }} PCS</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Target Delivery</span>
            <span class="text-sm font-medium text-gray-700 dark:text-white">{{ \Carbon\Carbon::parse($part->delivery_date)->format('d M Y') }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Selesai Produksi</span>
            <span class="text-sm font-medium text-gray-700 dark:text-white">
                @if($part->processes->count() > 0)
                    {{ \Carbon\Carbon::parse($part->processes->last()->actual_completion_date ?? \Carbon\Carbon::now())->format('d M Y') }}
                @else
                    N/A
                @endif
            </span>
        </div>
    </div>

    <form action="{{ route('checksheets.update', $checksheet->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="role" value="{{ $role }}">

        <div class="p-6">
            @if(!$isMGM)
            <!--=============================
                   QA / QC FORM 
            ==============================-->
            <div class="space-y-6 max-w-2xl mx-auto">
                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded text-sm text-blue-700 dark:text-blue-300">
                    <p class="font-semibold mb-1">Instruksi Quality Control:</p>
                    <p>Silakan isi persentase hitungan akurasi dimensi part dan lampirkan file laporan inspeksi fisik (PDF/Image).</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Hitungan Accuracy (%) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative w-48">
                        <input type="number" step="0.01" min="0" max="100" name="accuracy_percentage" required value="{{ old('accuracy_percentage', $checksheet->accuracy_percentage) }}"
                            class="w-full text-right pr-8 rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-lg font-bold text-gray-800 dark:bg-gray-700 dark:text-white pb-2 pt-2">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-bold">%</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Attachment / Laporan Inspeksi (Max 10MB) <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <div class="space-y-1 text-center">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-400 mb-2"></i>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 px-2 py-1">
                                    <span>Upload a file</span>
                                    <input id="file-upload" name="attachment_file" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500" id="file-name-display">PDF, PNG, JPG up to 10MB</p>
                        </div>
                    </div>
                    @if($checksheet->attachment_path)
                        <div class="mt-2 text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                            <i class="fa-solid fa-paperclip"></i> Existing file terlampir. Upload ulang untuk mengganti.
                        </div>
                    @endif
                </div>
            </div>
            @endif

            @if($isMGM)
            <!--=============================
                   MGM CHECKLIST FORM 
            ==============================-->
            <div class="mb-6">
                <!-- Data QC Sebelumnya (Read Only) -->
                <div class="flex flex-col md:flex-row gap-6 mb-6 p-4 rounded-lg bg-slate-50 border border-slate-200 dark:bg-gray-900 dark:border-gray-700">
                    <div>
                        <span class="block text-xs text-gray-500 uppercase font-semibold">Hasil Accuracy QC</span>
                        <span class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ $checksheet->accuracy_percentage ?? 'N/A' }}%</span>
                    </div>
                    @if($checksheet->attachment_path)
                    <div class="flex items-center">
                        <a href="{{ Storage::url($checksheet->attachment_path) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <i class="fa-solid fa-file-pdf text-red-500"></i> View Lampiran QC
                        </a>
                    </div>
                    @else
                    <div class="flex items-center text-sm text-gray-500 italic">
                        Tidak ada file lampiran QC.
                    </div>
                    @endif
                </div>

                <div class="mb-4">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Formulir Validasi Management (24 Point)</h3>
                    <p class="text-xs text-gray-500 mt-1">Hanya menampilkan poin-poin yang telah di-mapping ke part ini saat pendaftaran PO.</p>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-100 dark:bg-gray-700/80 text-gray-700 dark:text-gray-300 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-4 py-3 border-r dark:border-gray-600 w-12 text-center">No</th>
                                <th class="px-4 py-3 border-r dark:border-gray-600">Poin Pengecekan</th>
                                <th class="px-4 py-3 border-r dark:border-gray-600 w-48">Standard Parameter</th>
                                <th class="px-4 py-3 text-center w-32">Status OK/NG</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($checksheet->details as $index => $detail)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 border-r dark:border-gray-700 text-center text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 border-r dark:border-gray-700 font-medium text-gray-800 dark:text-gray-200 whitespace-normal min-w-[200px]">
                                    {{ $detail->point_check }}
                                </td>
                                <td class="px-4 py-3 border-r dark:border-gray-700 text-gray-600 dark:text-gray-400 whitespace-normal">
                                    {{ $detail->standard ?? '-' }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <select name="details[{{ $detail->id }}][row_result]"
                                            class="w-full text-xs py-1.5 px-2 font-bold border-gray-300 dark:border-gray-600 rounded shadow-sm focus:ring-1 focus:ring-blue-500 dark:bg-gray-800 dark:text-white
                                            @if($detail->row_result == 'OK') text-green-600 bg-green-50 dark:bg-green-900/20 
                                            @elseif($detail->row_result == 'NG') text-red-600 bg-red-50 dark:bg-red-900/20 @endif">
                                        <option value="" class="text-gray-400">- Pilih -</option>
                                        <option value="OK" class="text-green-600 font-bold" {{ $detail->row_result === 'OK' ? 'selected' : '' }}>OK</option>
                                        <option value="NG" class="text-red-600 font-bold" {{ $detail->row_result === 'NG' ? 'selected' : '' }}>NG</option>
                                    </select>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500 italic">
                                    Tidak ada point checking yang di-mapping ke part ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-purple-50 dark:bg-purple-900/10 p-4 rounded-lg border border-purple-100 dark:border-purple-800/30">
                    <div class="flex items-center gap-4">
                        <label class="font-bold text-gray-800 dark:text-white text-base">Keputusan Akhir (MGM Decision):</label>
                        <select name="final_result" required
                                class="rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-base py-2 pl-3 pr-10 font-bold text-gray-800 dark:bg-gray-800 dark:text-white dark:border-gray-600 min-w-[200px]">
                            <option value="Pending" {{ $checksheet->final_result == 'Pending' ? 'selected' : '' }}>PENDING</option>
                            <option value="OK" class="text-green-600" {{ $checksheet->final_result == 'OK' ? 'selected' : '' }}>✅ APPROVED (OK)</option>
                            <option value="NG" class="text-red-600" {{ $checksheet->final_result == 'NG' ? 'selected' : '' }}>❌ REJECTED (NG)</option>
                            <option value="Need Improvement" class="text-yellow-600" {{ $checksheet->final_result == 'Need Improvement' ? 'selected' : '' }}>⚠️ NEED IMPROVEMENT</option>
                        </select>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 rounded-b-lg">
            <a href="{{ route('tracking.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm text-sm font-medium">
                Batal
            </a>
            <button type="submit" class="px-5 py-2 {{ $isMGM ? 'bg-purple-600 hover:bg-purple-700' : 'bg-orange-600 hover:bg-orange-700' }} text-white rounded-lg transition shadow-sm font-semibold flex items-center gap-2 text-sm">
                <i class="fa-solid fa-floppy-disk"></i> {{ $isMGM ? 'Simpan & Approve (MGM)' : 'Submit Accuracy (QC)' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileUpload = document.getElementById('file-upload');
        const fileNameDisplay = document.getElementById('file-name-display');

        if(fileUpload) {
            fileUpload.addEventListener('change', function(e) {
                if(e.target.files.length > 0) {
                    fileNameDisplay.textContent = e.target.files[0].name;
                    fileNameDisplay.classList.add('text-blue-600', 'font-medium');
                } else {
                    fileNameDisplay.textContent = 'PDF, PNG, JPG up to 10MB';
                    fileNameDisplay.classList.remove('text-blue-600', 'font-medium');
                }
            });
        }
    });
</script>
@endpush
