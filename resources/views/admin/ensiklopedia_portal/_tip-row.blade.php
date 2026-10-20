<div class="tip-row flex flex-wrap gap-2 rounded-xl border border-green-100 bg-green-50/50 p-3">
    <input type="text" name="{{ $field }}[{{ $index }}][icon]" value="{{ $tip['icon'] ?? '' }}" required
           class="w-16 rounded-lg border border-gray-200 px-2 py-2 text-center text-sm" placeholder="🌿">
    <input type="text" name="{{ $field }}[{{ $index }}][judul]" value="{{ $tip['judul'] ?? '' }}" required
           class="min-w-[8rem] flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Judul">
    <input type="text" name="{{ $field }}[{{ $index }}][isi]" value="{{ $tip['isi'] ?? '' }}" required
           class="min-w-[12rem] flex-[2] rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Isi tip">
    <button type="button" class="remove-tip-row rounded-lg px-2 text-sm text-red-600 hover:bg-red-50" title="Hapus">✕</button>
</div>
