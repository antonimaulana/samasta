@extends('layouts.admin')

@section('title', $isNew ? 'Tambah FAQ' : 'Edit FAQ')
@section('header', $isNew ? 'Tambah FAQ' : 'Edit FAQ')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.faq.index') }}" class="text-sm font-semibold text-green-700 hover:underline">← Daftar FAQ</a>
    </div>

    <form action="{{ $isNew ? route('admin.faq.store') : route('admin.faq.update', $faq) }}"
          method="POST" class="mx-auto max-w-2xl space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        @unless($isNew)
            @method('PUT')
        @endunless

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Pertanyaan</label>
            <input type="text" name="question" value="{{ old('question', $faq->question) }}" required
                   class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Jawaban</label>
            <textarea name="answer" rows="6" required
                      class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">{{ old('answer', $faq->answer) }}</textarea>
        </div>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq->is_active ?? true))
                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
            <span class="text-sm font-medium text-gray-800">Tampilkan di beranda</span>
        </label>
        <button type="submit" class="w-full rounded-xl bg-green-600 py-3 text-sm font-bold text-white hover:bg-green-700">
            Simpan
        </button>
    </form>
@endsection
