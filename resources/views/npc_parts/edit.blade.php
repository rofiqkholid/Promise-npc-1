@extends('layouts.app')

@section('title', 'Edit Part')
@section('page_title', 'Master Data / Event / Edit Part')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-cube text-blue-500"></i> Form Edit Part Output
            </h2>
            <span class="text-sm font-medium bg-blue-100 text-blue-800 py-1 px-3 rounded-full">{{ $event->event_name }}</span>
        </div>

        <form action="{{ route('events.parts.update', [$event->id, $part->id]) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data Pencarian -->
                <div class="col-span-1 md:col-span-2 space-y-1 relative">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Cari Part No / Name dari DB Drawing (Gunakan jika ingin mengganti Part)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-search text-xs"></i>
                        </div>
                        <input type="text" id="part_search" autocomplete="off"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                            style="padding-left: 2.5rem;"
                            placeholder="Ketik Part No atau Part Name untuk mencari...">
                        
                        <!-- Search Results Dropdown -->
                        <div id="search_results" class="hidden absolute z-30 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <!-- Items go here -->
                        </div>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        PO No <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="po_no" required value="{{ old('po_no', $part->po_no) }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                    @error('po_no') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Part No <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="part_no_input" name="part_no" required value="{{ old('part_no', $part->part_no) }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                    @error('part_no') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Part Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="part_name_input" name="part_name" required value="{{ old('part_name', $part->part_name) }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                    @error('part_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Qty <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="qty" required min="1" value="{{ old('qty', $part->qty) }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Delivery Date <span class="text-red-500">*</span>
                    </label>
                    <!-- Carbon date format to Y-m-d for input[type=date] -->
                    <input type="date" name="delivery_date" required value="{{ old('delivery_date', \Carbon\Carbon::parse($part->delivery_date)->format('Y-m-d')) }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Actual Delivery Date
                    </label>
                    <input type="date" name="actual_delivery" value="{{ old('actual_delivery', $part->actual_delivery ? \Carbon\Carbon::parse($part->actual_delivery)->format('Y-m-d') : '') }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                </div>

                <div class="space-y-1">
                    <label for="process" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Process (Routing) <span class="text-red-500">*</span>
                    </label>
                    <select id="process" name="process" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                        <option value="">Pilih Alur Proses</option>
                        @foreach($processes as $proc)
                            <option value="{{ $proc->process_name }}" {{ old('process', $part->process) == $proc->process_name ? 'selected' : '' }}>
                                {{ $proc->process_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Department PIC <span class="text-red-500">*</span>
                    </label>
                    <select id="department" name="department" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                        <option value="">Pilih Department</option>
                        @foreach(\App\Models\NpcDepartment::where('is_active', true)->orderBy('name')->get() as $dept)
                            <option value="{{ $dept->name }}" {{ old('department', $part->department) == $dept->name ? 'selected' : '' }}>
                                {{ $dept->full_name ?? $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                        <option value="WAITING_DEPT_CONFIRM" {{ old('status', $part->status) == 'WAITING_DEPT_CONFIRM' ? 'selected' : '' }}>WAITING DEPT CONFIRM</option>
                        <option value="WAITING_QE_CHECK" {{ old('status', $part->status) == 'WAITING_QE_CHECK' ? 'selected' : '' }}>WAITING QE CHECK</option>
                        <option value="WAITING_MGM_CHECK" {{ old('status', $part->status) == 'WAITING_MGM_CHECK' ? 'selected' : '' }}>WAITING MGM CHECK</option>
                        <option value="FINISHED" {{ old('status', $part->status) == 'FINISHED' ? 'selected' : '' }}>FINISHED</option>
                    </select>
                </div>
                
                <div class="col-span-1 md:col-span-2 space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Condition
                    </label>
                    <input type="text" name="condition" value="{{ old('condition', $part->condition) }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                        placeholder="Kondisi terkini part (misal: OK / Delay)">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <a href="{{ route('events.parts.index', $event->id) }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Update Part
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('part_search');
        const searchResults = document.getElementById('search_results');
        const partNoInput = document.getElementById('part_no_input');
        const partNameInput = document.getElementById('part_name_input');
        const modelId = "{{ $event->model_id }}";
        
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
                    body: JSON.stringify({ search: query, model_id: modelId })
                })
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if(data.results && data.results.length > 0) {
                        data.results.forEach(product => {
                            let div = document.createElement('div');
                            div.className = 'px-4 py-3 hover:bg-blue-50 dark:hover:bg-slate-700 cursor-pointer text-sm text-slate-700 dark:text-slate-200 border-b border-gray-100 dark:border-gray-700 last:border-0';
                            div.innerHTML = `<span class="font-bold font-mono text-blue-600 dark:text-blue-400">${product.part_no}</span> - ${product.part_name}`;
                            div.addEventListener('click', function() {
                                partNoInput.value = product.part_no;
                                partNameInput.value = product.part_name;
                                searchInput.value = product.part_no;
                                searchResults.classList.add('hidden');
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

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    });
</script>
@endpush
