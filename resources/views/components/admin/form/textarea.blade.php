<textarea {{ $attributes->merge([
    'class' => 'w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500',
]) }}>{{ $slot }}</textarea>
