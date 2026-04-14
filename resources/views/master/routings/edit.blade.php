@extends('layouts.app')

@section('title', 'Edit Master Routing')
@section('page_title', 'Master Data / Edit Master Routing')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-route text-blue-500"></i> Form Edit Master Routing
            </h2>
        </div>

        <form action="{{ route('master.routings.update', $part->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                    <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-1">Part Terpilih</p>
                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ optional($part->product)->part_no }} - {{ optional($part->product)->part_name }}</p>
                    <p class="text-xs text-blue-500/80 dark:text-blue-300/80 mt-1 flex items-center gap-1 font-medium">
                        <i class="fa-solid fa-car"></i> {{ optional($part->vehicleModel)->name ?? 'Unknown Model' }}
                        <span class="mx-1">|</span>
                        <i class="fa-solid fa-building text-[10px]"></i> {{ optional(optional($part->vehicleModel)->customer)->name ?? 'Unknown Customer' }}
                    </p>
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Alur Proses (Process Sequence) <span class="text-red-500">*</span>
                        </label>
                        <button type="button" id="add_process_btn" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition text-xs font-medium flex items-center gap-1">
                            <i class="fa-solid fa-plus"></i> Tambah Proses
                        </button>
                    </div>

                    <div id="process_container" class="space-y-3">
                        @foreach($routings as $idx => $r)
                        <div class="process-item flex gap-3 items-center" data-index="{{ $idx }}">
                            <div class="w-10 text-center font-bold text-gray-400 dark:text-gray-500">{{ $idx + 1 }}.</div>
                            <div class="flex-1">
                                <select name="process_ids[]" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                                    <option value="">Pilih Proses</option>
                                    @foreach($processes as $proc)
                                        <option value="{{ $proc->id }}" {{ $r->process_id == $proc->id ? 'selected' : '' }}>
                                            {{ $proc->process_name }} ({{ $proc->department ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="remove-process w-10 text-center text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 p-2 rounded transition">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @error('process_ids') <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <a href="{{ route('master.routings.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Update Routing
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Dynamic Process Rows ---
        const container = document.getElementById('process_container');
        const addBtn = document.getElementById('add_process_btn');
        let processCount = {{ count($routings) }};

        const processOptions = `
            <option value="">Pilih Proses</option>
            @foreach($processes as $proc)
                <option value="{{ $proc->id }}">{{ $proc->process_name }} ({{ $proc->department ?? 'N/A' }})</option>
            @endforeach
        `;

        function updateSequenceNumbers() {
            const items = container.querySelectorAll('.process-item');
            items.forEach((item, idx) => {
                item.querySelector('.font-bold').textContent = (idx + 1) + '.';
            });
        }

        addBtn.addEventListener('click', function() {
            processCount++;
            const itemHtml = `
                <div class="process-item flex gap-3 items-center" data-index="${processCount}">
                    <div class="w-10 text-center font-bold text-gray-400 dark:text-gray-500"></div>
                    <div class="flex-1">
                        <select name="process_ids[]" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                            ${processOptions}
                        </select>
                    </div>
                    <button type="button" class="remove-process w-10 text-center text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 p-2 rounded transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', itemHtml);
            updateSequenceNumbers();
        });

        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-process')) {
                const items = container.querySelectorAll('.process-item');
                if (items.length > 1) {
                    e.target.closest('.process-item').remove();
                    updateSequenceNumbers();
                } else {
                    alert('Minimal harus ada 1 proses!');
                }
            }
        });

        updateSequenceNumbers();
    });
</script>
@endpush
