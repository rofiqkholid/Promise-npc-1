@extends('layouts.app')

@section('title', 'Edit Master Event')
@section('page_title', 'Master Data / Master Events / Edit')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-flag text-blue-500"></i> Form Edit Master Event
            </h2>
        </div>

        <form action="{{ route('master.events.update', $event->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Customer Select -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Customer <span class="text-red-500">*</span>
                </label>
                <select name="customer_id" id="customer_select" required data-placeholder="Pilih Customer..."
                    class="select2 w-full">
                    <option value="">Pilih Customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $event->customer_id) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <!-- Model Select -->
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Vehicle Model <span class="text-red-500">*</span>
                </label>
                <select name="model_id" id="model_select" required data-placeholder="Pilih Model..."
                    class="select2 w-full">
                    <option value="">Pilih Model...</option>
                    @foreach($models as $model)
                        <option value="{{ $model->id }}" {{ old('model_id', $event->model_id) == $model->id ? 'selected' : '' }}>
                            {{ $model->name }}
                        </option>
                    @endforeach
                </select>
                @error('model_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Event Project <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" required value="{{ old('name', $event->name) }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <a href="{{ route('master.events.index') }}" class="px-4 py-2 border border-slate-300 dark:border-gray-600 rounded-lg text-slate-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700 transition">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md shadow-blue-500/20 transition">Update Event</button>
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
            $(modelSelect).trigger('change.select2');
            
            if (!customerId) {
                modelSelect.innerHTML = '<option value="">Pilih Model</option>';
                modelSelect.disabled = true;
                $(modelSelect).trigger('change.select2');
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
                $(modelSelect).trigger('change.select2');
            })
            .catch(error => {
                console.error('Error fetching models:', error);
                modelSelect.innerHTML = '<option value="">-- Gagal memuat data --</option>';
                $(modelSelect).trigger('change.select2');
            });
        }

        $('#customer_select').on('change', function() {
            loadModels(this.value);
        });

        if ("{{ old('customer_id') }}" && "{{ old('customer_id') }}" !== "{{ $event->customer_id }}") {
             loadModels("{{ old('customer_id') }}", oldModelId);
        }
    });
</script>
@endpush
