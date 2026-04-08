@extends('layouts.app')

@section('title', 'Edit Event')
@section('page_title', 'Master Data / Edit Event')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-blue-500"></i> Form Edit Event
            </h2>
        </div>

        <form action="{{ route('events.update', $event->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Event Name / Keterangan Event <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-tag text-xs"></i>
                    </div>
                    <input type="text" name="event_name" required value="{{ old('event_name', $event->event_name) }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white" style="padding-left: 2.5rem;">
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
                                <option value="{{ $customer->id }}" {{ (old('customer_id', $event->customer_id) == $customer->id) ? 'selected' : '' }}>
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
                            @foreach($models as $model)
                                <option value="{{ $model->id }}" {{ (old('model_id', $event->model_id) == $model->id) ? 'selected' : '' }}>
                                    {{ $model->name }}
                                </option>
                            @endforeach
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
                        <option value="{{ $target->target_name }}" {{ old('delivery_to', $event->delivery_to) == $target->target_name ? 'selected' : '' }}>
                            {{ $target->target_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <a href="{{ route('events.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Update Event
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
        const oldModelId = "{{ old('model_id', $event->model_id) }}";

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

        // We already preload the models in PHP, so we don't strictly *need* to call loadModels on load
        // UNLESS there's an old() validation failure that changed the customer but didn't refresh models.
        if ("{{ old('customer_id') }}" && "{{ old('customer_id') }}" !== "{{ $event->customer_id }}") {
             loadModels("{{ old('customer_id') }}", oldModelId);
        }
    });
</script>
@endpush
