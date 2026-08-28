@props([
    'label',
    'alignTop' => false,
])

<tr>
    <td @class([
        'px-4 py-3 text-sm font-medium text-gray-700',
        'align-top' => $alignTop,
    ])>{{ $label }}</td>
    <td @class([
        'px-4 py-3 text-sm text-gray-900',
        'align-top whitespace-pre-line' => $alignTop,
    ])>{{ $slot }}</td>
</tr>
