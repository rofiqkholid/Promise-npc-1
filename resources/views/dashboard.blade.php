@extends('layouts.app')

@section('title', 'NPC Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="dashboard-container flex-1 flex flex-col items-center justify-center min-h-[400px]">
    <div class="text-center p-8 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 shadow-sm max-w-lg w-full">
        <div class="w-16 h-16 bg-primary-50 dark:bg-primary-900/20 rounded-full flex items-center justify-center text-primary-600 dark:text-primary-400 mx-auto mb-4 text-2xl">
            <i class="fa-solid fa-gauge-high"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Welcome to Promise NPC</h2>
        <p class="text-slate-500 dark:text-gray-400">Dashboard content is under development.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Dashboard scripts cleared for now.
</script>
@endpush