@extends('layouts.app')

@section('title', 'Tambah Event')
@section('page_title', 'Master Data / Tambah Event')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-clipboard-list text-blue-500"></i> Form Tambah Event Baru
            </h2>
        </div>

        <form action="{{ route('events.store') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Event Name / Keterangan Event <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-tag text-xs"></i>
                    </div>
                    <input type="text" name="event_name" required value="{{ old('event_name') }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                        style="padding-left: 2.5rem;"
                        placeholder="Contoh: PCLC25-0810 Local Parts Order MMC 28MY...">
                </div>
                @error('event_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customer Select -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Customer <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-building text-xs"></i>
                        </div>
                        <select name="customer_id" id="customer_select" required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white" style="padding-left: 2.5rem;">
                            <option value="">Pilih Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('customer_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Model Select -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Vehicle Model <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-car text-xs"></i>
                        </div>
                        <select name="model_id" id="model_select" required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white" style="padding-left: 2.5rem;">
                            <option value="">Pilih Model</option>
                        </select>
                    </div>
                    @error('model_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="space-y-1">
                <label for="delivery_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tujuan Pengiriman (Delivery To)
                </label>
                <select id="delivery_to" name="delivery_to"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                    <option value="">Pilih Tujuan (Opsional)</option>
                    @foreach($delivery_targets as $target)
                        <option value="{{ $target->target_name }}" {{ old('delivery_to') == $target->target_name ? 'selected' : '' }}>
                            {{ $target->target_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Part Details Section (Dynamic) -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-md font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-cubes text-indigo-500"></i> Detail Parts & PO
                    </h3>
                    <button type="button" id="add_part_btn" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition text-sm font-medium flex items-center gap-1">
                        <i class="fa-solid fa-plus"></i> Tambah Part
                    </button>
                </div>
                
                <div id="parts_container" class="space-y-4">
                    <!-- Parts will be dynamically added here -->
                    <div class="text-center py-6 text-gray-500 dark:text-gray-400 text-sm" id="empty_parts_msg">
                        Belum ada part yang ditambahkan. Klik tombol "Tambah Part" di atas.
                    </div>
                </div>
            </div>

            <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <a href="{{ route('events.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Event
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const customerSelect = document.getElementById('customer_select');
        const modelSelect = document.getElementById('model_select');
        const oldModelId = "{{ old('model_id') }}";

        function loadModels(customerId, selectedModelId = null) {
            modelSelect.innerHTML = '<option value="">Memuat...</option>';
            modelSelect.disabled = true;
            
            if (!customerId) {
                modelSelect.innerHTML = '<option value="">Pilih Model</option>';
                modelSelect.disabled = true;
                return;
            }

            fetch("{{ route('api.data.models') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ customer_id: customerId })
            })
            .then(response => response.json())
            .then(data => {
                modelSelect.innerHTML = '<option value="">Pilih Model</option>';
                if(data.results && data.results.length > 0) {
                    data.results.forEach(model => {
                        let isSelected = selectedModelId == model.id ? 'selected' : '';
                        modelSelect.innerHTML += `<option value="${model.id}" ${isSelected}>${model.text}</option>`;
                    });
                    modelSelect.disabled = false;
                } else {
                    modelSelect.innerHTML = '<option value="">Tidak ada model tersedia</option>';
                }
            })
            .catch(error => {
                console.error('Error fetching models:', error);
                modelSelect.innerHTML = '<option value="">-- Gagal memuat data --</option>';
            });
        }

        customerSelect.addEventListener('change', function() {
            loadModels(this.value);
        });

        // Load models if customer was already selected (e.g. back validation error)
        if (customerSelect.value) {
            loadModels(customerSelect.value, oldModelId);
        }

        // --- Dynamic Parts Logic ---
        const partsContainer = document.getElementById('parts_container');
        const addPartBtn = document.getElementById('add_part_btn');
        const emptyMsg = document.getElementById('empty_parts_msg');
        let partIndex = 0;

        const masterCheckpoints = @json($checkpoints);

        addPartBtn.addEventListener('click', function() {
            if(emptyMsg) emptyMsg.style.display = 'none';
            
            // Generate HTML for Checkpoints
            let checkpointsHtml = '';
            masterCheckpoints.forEach(cp => {
                checkpointsHtml += `
                    <label class="inline-flex items-center bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-3 py-2 rounded cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-600 transition">
                        <input type="checkbox" name="parts[${partIndex}][checkpoints][]" value="${cp.id}" class="rounded text-blue-600 focus:ring-blue-500 bg-white border-gray-300 dark:border-gray-500 dark:bg-gray-800 dark:checked:bg-blue-600 w-4 h-4" checked>
                        <span class="ml-2 text-xs text-gray-700 dark:text-gray-300 font-medium truncate w-32" title="${cp.check_item}">${cp.check_item}</span>
                    </label>
                `;
            });

            const template = `
                <div class="part-item bg-slate-50 dark:bg-gray-800/80 p-4 rounded-lg border border-slate-200 dark:border-gray-700 relative">
                    <button type="button" class="remove-part absolute top-3 right-3 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 p-1.5 rounded transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Nomor PO</label>
                            <input type="text" name="parts[${partIndex}][po_no]" required class="w-full text-sm rounded border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="No. PO">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Target Delivery</label>
                            <input type="date" name="parts[${partIndex}][delivery_date]" required class="w-full text-sm rounded border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div class="space-y-1 relative">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Part Number</label>
                            <input type="text" class="part-no-display w-full text-sm rounded border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Cari Part No..." autocomplete="off">
                            <input type="hidden" name="parts[${partIndex}][part_no]" class="part-no-input" required>
                            <input type="hidden" name="parts[${partIndex}][part_name]" class="part-name-input">
                            <div class="part-autocomplete hidden absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg max-h-52 overflow-y-auto text-sm"></div>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Qty</label>
                            <input type="number" name="parts[${partIndex}][qty]" min="1" value="1" required class="w-full text-sm rounded border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                    
                    <div class="pt-3 border-t border-slate-200 dark:border-gray-700">
                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-2">Mapping Master Checkpoint MGM:</p>
                        <div class="flex flex-wrap gap-2">
                            ${checkpointsHtml}
                        </div>
                    </div>
                </div>
            `;
            
            partsContainer.insertAdjacentHTML('beforeend', template);

            // Auto-fill PO & date from previous row
            const allItems = partsContainer.querySelectorAll('.part-item');
            if (allItems.length >= 2) {
                const prev = allItems[allItems.length - 2];
                const curItem = allItems[allItems.length - 1];
                const prevPo = prev.querySelector('input[name*="[po_no]"]');
                const prevDate = prev.querySelector('input[name*="[delivery_date]"]');
                if (prevPo) curItem.querySelector('input[name*="[po_no]"]').value = prevPo.value;
                if (prevDate) curItem.querySelector('input[name*="[delivery_date]"]').value = prevDate.value;
            }

            // Custom autocomplete for part number
            const partItem = partsContainer.lastElementChild;
            const displayInput = partItem.querySelector('.part-no-display');
            const partNoInput = partItem.querySelector('.part-no-input');
            const partNameInput = partItem.querySelector('.part-name-input');
            const dropdown = partItem.querySelector('.part-autocomplete');
            let acTimer;

            displayInput.addEventListener('input', function() {
                clearTimeout(acTimer);
                
                // Hapus nilai part yang sudah dipilih sebelumnya jika user mengetik ulang
                partNoInput.value = '';
                partNameInput.value = '';
                
                const q = this.value.trim();
                if (q.length < 2) { dropdown.classList.add('hidden'); return; }
                acTimer = setTimeout(() => {
                    fetch("{{ route('api.data.products') }}", {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        body: JSON.stringify({ search: q, model_id: document.getElementById('model_select').value })
                    })
                    .then(r => r.json())
                    .then(data => {
                        dropdown.innerHTML = '';
                        if (data.results && data.results.length > 0) {
                            data.results.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0';
                                div.innerHTML = `<span class="font-semibold font-mono text-blue-600 dark:text-blue-400 text-xs">${item.part_no}</span> <span class="text-gray-600 dark:text-gray-300 text-xs">${item.part_name}</span>`;
                                div.addEventListener('mousedown', function(e) {
                                    e.preventDefault();
                                    displayInput.value = item.part_no;
                                    partNoInput.value = item.part_no;
                                    partNameInput.value = item.part_name;
                                    dropdown.classList.add('hidden');
                                });
                                dropdown.appendChild(div);
                            });
                            dropdown.classList.remove('hidden');
                        } else {
                            dropdown.innerHTML = '<div class="px-3 py-2 text-gray-400 text-xs italic">Tidak ada hasil</div>';
                            dropdown.classList.remove('hidden');
                        }
                    });
                }, 300);
            });

            displayInput.addEventListener('blur', function() {
                setTimeout(() => dropdown.classList.add('hidden'), 150);
            });

            partIndex++;
        });

        partsContainer.addEventListener('click', function(e) {
            if(e.target.closest('.remove-part')) {
                e.target.closest('.part-item').remove();
                if(partsContainer.querySelectorAll('.part-item').length === 0) {
                    if(emptyMsg) emptyMsg.style.display = 'block';
                }
            }
        });
        
        // Auto-add 1 part on load
        addPartBtn.click();
    });
</script>
@endpush
