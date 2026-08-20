@extends('layouts.admin')

@section('title', 'Log Aktivitas')
@section('header', 'Log Aktivitas Admin')

@section('content')
    <p class="mb-6 text-sm text-gray-600">Catatan perubahan data yang dilakukan pengguna admin (create, update, delete).</p>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Waktu</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Pengguna</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Aksi</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Subjek</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                {{ $log->created_at->translatedFormat('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $log->user?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500">{{ $log->user?->roleLabel() }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-gray-100 px-2 py-0.5 font-mono text-xs text-gray-700">{{ $log->method }}</span>
                                <p class="mt-1 text-xs text-gray-600">{{ $log->action }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                @if ($log->subject_type)
                                    {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">Belum ada aktivitas tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-gray-200 px-4 py-3">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
