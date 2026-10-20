<footer class="border-t border-green-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-8 sm:flex-row sm:px-6 lg:px-8">
        <div class="text-center sm:text-left">
            <p class="font-bold text-green-700">{{ config('app.name') }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ config('app.full_name') }} · Kota Batam</p>
        </div>
        <p class="text-xs text-gray-400">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </div>
</footer>
