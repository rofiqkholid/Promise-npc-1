@extends('layouts.app')

@section('title', 'Tambah Master Routing')
@section('page_title', 'Master Data / Tambah Master Routing')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-route text-blue-500"></i> Form Tambah Master Routing
            </h2>
        </div>

        <form action="{{ route('master.routings.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <div class="space-y-4">
                <!-- Part Selection -->
                <div class="space-y-1 relative">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Pilih Part (Drawing) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-search text-xs"></i>
                        </div>
                        <input type="text" id="part_search" autocomplete="off"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                            style="padding-left: 2.5rem;"
                            placeholder="Ketik Part No atau Part Name untuk mencari...">
                        
                        <input type="hidden" id="part_id" name="part_id" required value="{{ old('part_id') }}">
                        
                        <div id="search_results" class="hidden absolute z-30 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <!-- Items go here -->
                        </div>
                    </div>
                    @error('part_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    
                    <div id="selected_part_display" class="mt-2 text-sm font-medium text-blue-600 dark:text-blue-400 hidden">
                        <i class="fa-solid fa-check-circle mr-1"></i> Part terpilih: <span id="part_label_text"></span>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Alur Proses (Process Sequence) <span class="text-red-500">*</span>
                        </label>
                        <button type="button" id="add_process_btn" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition text-xs font-medium flex items-center gap-1">
                            <i class="fa-solid fa-plus"></i> Tambah Proses
                        </button>
                    </div>

                    <div id="process_container" class="space-y-3">
                        <!-- Dynamic processes -->
                        <div class="process-item flex gap-3 items-center" data-index="0">
                            <div class="w-10 text-center font-bold text-gray-400 dark:text-gray-500">1.</div>
                            <div class="flex-1">
                                <select name="process_ids[]" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                                    <option value="">Pilih Proses</option>
                                    @foreach($processes as $proc)
                                        <option value="{{ $proc->id }}">{{ $proc->process_name }} ({{ $proc->department ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="remove-process w-10 text-center text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 p-2 rounded transition">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    @error('process_ids') <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <a href="{{ route('master.routings.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Routing
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Part Autocomplete ---
        const searchInput = document.getElementById('part_search');
        const searchResults = document.getElementById('search_results');
        const partIdInput = document.getElementById('part_id');
        const partDisplay = document.getElementById('selected_part_display');
        const partLabelText = document.getElementById('part_label_text');
        
        let debounceTimer;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value;

            if (query.length < 2) {
                searchResults.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch("{{ route('api.data.products') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ search: query })
                })
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if(data.results && data.results.length > 0) {
                        data.results.forEach(product => {
                            let div = document.createElement('div');
                            div.className = 'px-4 py-3 hover:bg-blue-50 dark:hover:bg-slate-700 cursor-pointer text-sm text-slate-700 dark:text-slate-200 border-b border-gray-100 dark:border-gray-700 last:border-0';
                            div.innerHTML = `<span class="font-bold font-mono text-blue-600 dark:text-blue-400">${product.part_no}</span> - ${product.part_name} <br> <span class="text-xs text-gray-500 mt-0.5 inline-block"><i class="fa-solid fa-car mr-1"></i> ${product.model_name} | <i class="fa-solid fa-building mr-1"></i> ${product.customer_name}</span>`;
                            div.addEventListener('click', function() {
                                partIdInput.value = product.id;
                                searchInput.value = product.part_no;
                                searchResults.classList.add('hidden');
                                partDisplay.classList.remove('hidden');
                                partLabelText.innerHTML = `${product.part_no} - ${product.part_name} <span class="text-gray-500 text-xs ml-1">(${product.model_name})</span>`;
                            });
                            searchResults.appendChild(div);
                        });
                        searchResults.classList.remove('hidden');
                    } else {
                        searchResults.innerHTML = '<div class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 italic">Tidak ada part ditemukan...</div>';
                        searchResults.classList.remove('hidden');
                    }
                })
                .catch(error => console.error('Error fetching products:', error));
            }, 300);
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });

        // --- Dynamic Process Rows ---
        const container = document.getElementById('process_container');
        const addBtn = document.getElementById('add_process_btn');
        let processCount = 1;

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
