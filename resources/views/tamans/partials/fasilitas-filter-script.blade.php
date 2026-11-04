@push('scripts')
    <script>
        document.querySelectorAll('.fasilitas-filter-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const badge = this.nextElementSibling;
                if (this.checked) {
                    badge.classList.remove('border-green-200', 'bg-green-50', 'text-green-800', 'hover:border-green-400', 'hover:bg-green-100');
                    badge.classList.add('border-green-600', 'bg-green-600', 'text-white', 'shadow-sm');
                } else {
                    badge.classList.add('border-green-200', 'bg-green-50', 'text-green-800', 'hover:border-green-400', 'hover:bg-green-100');
                    badge.classList.remove('border-green-600', 'bg-green-600', 'text-white', 'shadow-sm');
                }
            });
        });
    </script>
@endpush
