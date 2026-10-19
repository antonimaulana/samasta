<tr data-member-row="{{ $index }}" class="border-b border-gray-100">
    <td class="px-2 py-2 align-top">
        @if (! empty($member['id']))
            <input type="hidden" name="members[{{ $index }}][id]" value="{{ $member['id'] }}">
        @endif
        <input type="text" name="members[{{ $index }}][nama]" value="{{ old('members.'.$index.'.nama', $member['nama'] ?? '') }}"
               placeholder="Nama lengkap"
               class="w-full min-w-[10rem] rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
        @error('members.'.$index.'.nama')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </td>
    <td class="px-2 py-2 align-top">
        <input type="text" name="members[{{ $index }}][nip]" value="{{ old('members.'.$index.'.nip', $member['nip'] ?? '') }}"
               inputmode="numeric" maxlength="20" placeholder="18 digit"
               class="w-full min-w-[9rem] rounded-lg border border-gray-300 px-2 py-1.5 text-sm font-mono focus:border-green-500 focus:ring-1 focus:ring-green-500">
        @error('members.'.$index.'.nip')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </td>
    <td class="px-2 py-2 align-top">
        <input type="text" name="members[{{ $index }}][jabatan]" value="{{ old('members.'.$index.'.jabatan', $member['jabatan'] ?? '') }}"
               placeholder="Jabatan"
               class="w-full min-w-[8rem] rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:border-green-500 focus:ring-1 focus:ring-green-500">
    </td>
    <td class="px-2 py-2 align-top text-right">
        <button type="button" data-remove-member
                class="rounded border border-gray-300 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50">
            Hapus
        </button>
    </td>
</tr>
