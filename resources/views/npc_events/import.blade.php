@extends('layouts.app')

@section('title', 'Import Data Event & Parts')
@section('page_title', 'Master Data / Import Event')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 max-w-3xl mx-auto">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            <i class="fa-solid fa-file-excel text-green-600 mr-2"></i> Import PO / Data Excel
        </h2>
    </div>

    <form action="{{ route('events.import.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="p-6 space-y-6">

            <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg border border-blue-100 dark:border-blue-800 text-sm text-blue-800 dark:text-blue-300">
                <p class="font-semibold mb-2"><i class="fa-solid fa-circle-info mr-1"></i> Information Format Excel:</p>
                <p class="mb-2">Pastikan kolom Excel mengikuti urutan berikut (Baris pertama dianggap Header dan akan dilewati):</p>
                <ol class="list-decimal pl-5 space-y-1 font-mono text-xs mt-2 relative z-10 w-fit p-3 bg-white dark:bg-slate-800 rounded shadow-sm border border-blue-200 dark:border-blue-700 text-slate-700 dark:text-slate-300">
                    <li>PO NO</li>
                    <li>PART NO</li>
                    <li>PART NAME</li>
                    <li>QTY</li>
                    <li>DELV DATE (YYYY-MM-DD / Format Date)</li>
                    <li>PROCESS (Contoh: SUPP, STAMPING)</li>
                </ol>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customer Selection -->
                <div class="space-y-1">
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Customer <span class="text-red-500">*</span>
                    </label>
                    <select id="customer_id" name="customer_id" required data-placeholder="Select Customer..."
                        class="select2 w-full">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Model Selection (Dinamis via JS sama seperti form create) -->
                <div class="space-y-1">
                    <label for="model_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Model <span class="text-red-500">*</span>
                    </label>
                    <select id="model_id" name="model_id" required disabled data-placeholder="Select Model..."
                        class="select2 w-full">
                        <option value="">Select Customer Terlebih Dahulu</option>
                    </select>
                </div>
                
                <!-- Category Select -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Category Event <span class="text-red-500">*</span>
                    </label>
                    <select name="customer_category_id" id="category_select" required disabled data-placeholder="Select Category Event..."
                        class="select2 w-full">
                        <option value="">Select Category</option>
                    </select>
                </div>

                <!-- Delivery Group Select -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Delivery Group (GR) <span class="text-red-500">*</span>
                    </label>
                    <select name="delivery_group_id" id="delivery_group_id" required data-placeholder="Select Delivery Group..."
                        class="select2 w-full">
                        <option value="">Select Grup</option>
                        @foreach($delivery_groups as $group)
                            <option value="{{ $group->id }}" {{ old('delivery_group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>



            <div class="space-y-1">
                <label for="delivery_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tujuan Pengiriman (Delivery To)
                </label>
                <select id="delivery_to" name="delivery_to" data-placeholder="Select Tujuan..."
                    class="select2 w-full">
                    <option value="">Select Tujuan (Optional)</option>
                    @foreach($delivery_targets as $target)
                        <option value="{{ $target->target_name }}" {{ old('delivery_to') == $target->target_name ? 'selected' : '' }}>
                            {{ $target->target_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <!-- File Upload -->
            <div class="space-y-1">
                <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Upload File Excel <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg">
                    <div class="space-y-1 text-center">
                        <i class="fa-solid fa-file-excel text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
                        <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                            <label for="file" class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 p-1">
                                <span>Select file .xlsx, .xls</span>
                                <input id="file" name="file" type="file" class="sr-only" required accept=".xlsx, .xls, .csv">
                            </label>
                            <p class="pl-1 pt-1">atau drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-500">Maks. 10MB</p>
                        <p id="file-name-display" class="text-sm font-semibold text-green-600 dark:text-green-400 mt-2 hidden"></p>
                    </div>
                </div>
            </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 rounded-b-lg">
            <a href="{{ route('events.index') }}" class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition flex items-center gap-2" onclick="return confirm('Start import process to database? This will create an Event and insert Parts in it.');">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload & Eksekusi
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Logic untuk Dropdown Model & Category Dinamis
    $('#customer_id').on('change', function() {
        const customerId = this.value;
        const modelSelect = document.getElementById('model_id');
        const categorySelect = document.getElementById('category_select');
        
        // Reset and show loading state
        modelSelect.innerHTML = '<option value="">Memuat data...</option>';
        modelSelect.disabled = true;
        $(modelSelect).trigger('change.select2');
        
        categorySelect.innerHTML = '<option value="">Memuat data...</option>';
        categorySelect.disabled = true;
        $(categorySelect).trigger('change.select2');
        


        if(customerId) {
            // Menggunakan proxy API Internal untuk menghindari CORS
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
                modelSelect.innerHTML = '<option value="">Select Model</option>';
                if(data.results && data.results.length > 0) {
                    data.results.forEach(model => {
                        modelSelect.innerHTML += `<option value="${model.id}">${model.text}</option>`;
                    });
                    modelSelect.disabled = false;
                } else {
                    modelSelect.innerHTML = '<option value="">No model found</option>';
                }
                $(modelSelect).trigger('change.select2');
            })
            .catch(error => {
                console.error('Error:', error);
                modelSelect.innerHTML = '<option value="">Failed memuat data</option>';
                $(modelSelect).trigger('change.select2');
            });
            
            // Category AJAX
            fetch("{{ route('api.data.customer-categories') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ customer_id: customerId })
            })
            .then(response => response.json())
            .then(data => {
                categorySelect.innerHTML = '<option value="">Select Category</option>';
                if(data.results && data.results.length > 0) {
                    data.results.forEach(cat => {
                        categorySelect.innerHTML += `<option value="${cat.id}">${cat.text}</option>`;
                    });
                    categorySelect.disabled = false;
                } else {
                    categorySelect.innerHTML = '<option value="">Category not found</option>';
                }
                $(categorySelect).trigger('change.select2');
            })
            .catch(error => {
                console.error('Error:', error);
                categorySelect.innerHTML = '<option value="">Failed memuat data</option>';
                $(categorySelect).trigger('change.select2');
            });
            
        } else {
            modelSelect.innerHTML = '<option value="">Select Customer Terlebih Dahulu</option>';
            $(modelSelect).trigger('change.select2');
            categorySelect.innerHTML = '<option value="">Select Category</option>';
            $(categorySelect).trigger('change.select2');
        }
    });



    // Logika menampilkan nama file Excel saat diselect
    document.getElementById('file').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        const display = document.getElementById('file-name-display');
        if (fileName) {
            display.textContent = "Selected File: " + fileName;
            display.classList.remove('hidden');
        } else {
            display.classList.add('hidden');
        }
    });
</script>
@endpush

