@extends('layouts.app')

@section('title', 'Master Data Event Utama')
@section('page_title', 'Master Data / Master Events')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-flag text-blue-500"></i> Daftar Master Event Project
        </h2>
        <a href="{{ route('master.events.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Tambah Master Event
        </a>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">#</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Nama Event</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Customer</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Model</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($masterEvents as $index => $event)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $masterEvents->firstItem() + $index }}</td>
                        <td class="px-6 py-4 text-slate-800 dark:text-slate-200 font-medium">
                            {{ $event->name }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                            {{ optional($event->customer)->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                            {{ optional($event->vehicleModel)->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('master.events.edit', $event->id) }}" class="text-blue-500 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-gray-700 p-2 rounded-md transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('master.events.destroy', $event->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Master Event ini? Jika sudah digunakan di transaksi, penghapusan akan ditolak.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-md transition" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-solid fa-flag text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>Belum ada Master Event yang didaftarkan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($masterEvents->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $masterEvents->links() }}
    </div>
    @endif
</div>
@endsection

