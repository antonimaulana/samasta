@props([
    'name',
    'id',
    'selected' => '',
    'selectedLabel' => '',
    'placeholder' => 'Ketik untuk mencari...',
    'emptyText' => 'Tidak ada taman yang cocok.',
    'options' => [],
    'required' => false,
    'inputClass' => 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500',
])

<div class="searchable-select relative" data-searchable-select>
    <input type="text"
           id="{{ $id }}_search"
           value="{{ $selectedLabel }}"
           autocomplete="off"
           placeholder="{{ $placeholder }}"
           class="{{ $inputClass }}"
           data-searchable-input>
    <input type="hidden"
           name="{{ $name }}"
           id="{{ $id }}"
           value="{{ $selected }}"
           @if ($required) data-searchable-required="true" @endif
           data-searchable-value>
    <ul class="absolute z-20 mt-1 hidden max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
        data-searchable-list
        data-empty-text="{{ $emptyText }}">
        @foreach ($options as $option)
            <li data-value="{{ $option['value'] }}"
                data-label="{{ $option['label'] }}"
                data-search="{{ $option['search'] }}"
                @if (! empty($option['kelurahan_id'])) data-kelurahan-id="{{ $option['kelurahan_id'] }}" @endif
                @class([
                    'cursor-pointer px-3 py-2 text-sm hover:bg-green-50',
                    'bg-green-50 font-medium text-green-800' => (string) $selected === (string) $option['value'],
                    'text-gray-800' => (string) $selected !== (string) $option['value'],
                ])>
                @if (! empty($option['html']))
                    {!! $option['html'] !!}
                @else
                    {{ $option['label'] }}
                @endif
            </li>
        @endforeach
    </ul>
</div>
