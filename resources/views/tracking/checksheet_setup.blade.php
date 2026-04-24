@extends('layouts.app')

@section('title', 'Setup Master Checksheet')
@section('page_title', 'Master Data / Setup Checksheet / ' . $product->part_no)

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-5xl mx-auto">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                <i class="fa-solid fa-list-check text-blue-500 mr-2"></i> Mapping Master Checksheet
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                <strong>Part No:</strong> <span class="text-blue-600 dark:text-blue-400 font-bold">{{ $product->part_no }}</span> | <strong>Name:</strong> {{ $product->part_name }} | <strong>Customer:</strong> <span class="font-bold text-gray-800 dark:text-gray-200">{{ optional($product->customer)->code ?? '-' }}</span> | <strong>Model:</strong> <span class="font-medium text-gray-800 dark:text-gray-200">{{ optional($product->vehicleModel)->name ?? '-' }}</span>
            </p>
        </div>
        <div>
            <a href="{{ route('master.checksheets.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded shadow-sm text-sm font-medium transition dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 border border-gray-300 dark:border-gray-600">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    
    <form action="{{ route('checksheets.setup.update', $product->id) }}" method="POST">
        @csrf
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/80 text-gray-800 dark:text-gray-200 uppercase text-xs tracking-wider border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="px-6 py-4 w-16 text-center">
                            <input type="checkbox" id="checkAll" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        </th>
                        <th class="px-6 py-4 w-20 text-center">Master No</th>
                        <th class="px-6 py-4">Point Check</th>
                        <th class="px-6 py-4 w-[400px]">Custom Standard / Parameter</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($masterPoints as $point)
                        @php
                            // Determine if checked
                            $isChecked = false;
                            if ($isFirstTime) {
                                // Default check all
                                $isChecked = true;
                            } else {
                                $isChecked = array_key_exists($point->id, $mappedData);
                            }
                            // Determine standard text
                            $stdText = $mappedData[$point->id] ?? '';
                        @endphp
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" name="points[{{ $point->id }}][is_checked]" value="1" {{ $isChecked ? 'checked' : '' }} class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-500">{{ $point->point_number }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-800 dark:text-gray-200">{{ $point->check_item }}</td>
                            <td class="px-6 py-4">
                                <input type="text" name="points[{{ $point->id }}][custom_standard]" value="{{ $stdText }}" placeholder="Leave blank if there is no standard" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:text-white form-input p-2 font-medium {{ $isChecked ? '' : 'opacity-50' }}" {{ $isChecked ? '' : 'readonly' }} onfocus="this.select()">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 rounded-b-lg">
            <a href="{{ route('master.checksheets.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm text-sm font-medium">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-sm font-bold flex items-center gap-2 text-sm shadow-md">
                <i class="fa-solid fa-floppy-disk"></i> Save Mapping Poin
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');

    // Default set Check All if all rows are checked
    function updateCheckAllState() {
        const total = rowCheckboxes.length;
        const checked = document.querySelectorAll('.row-checkbox:checked').length;
        checkAll.checked = (total > 0 && total === checked);
    }
    
    updateCheckAllState();

    checkAll.addEventListener('change', function() {
        rowCheckboxes.forEach(cb => {
            cb.checked = this.checked;
            toggleInputState(cb);
        });
    });

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateCheckAllState();
            toggleInputState(this);
        });
    });

    function toggleInputState(checkbox) {
        const input = checkbox.closest('tr').querySelector('input[type="text"]');
        if(checkbox.checked) {
            input.removeAttribute('readonly');
            input.classList.remove('opacity-50');
            input.classList.add('bg-white', 'dark:bg-gray-800');
            input.classList.remove('bg-gray-100', 'dark:bg-gray-900');
        } else {
            input.setAttribute('readonly', 'readonly');
            input.classList.add('opacity-50');
            input.classList.remove('bg-white', 'dark:bg-gray-800');
            input.classList.add('bg-gray-100', 'dark:bg-gray-900');
        }
    }
    
    // Initial setup
    rowCheckboxes.forEach(cb => toggleInputState(cb));
});
</script>
@endpush
