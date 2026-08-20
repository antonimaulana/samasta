<a href="{{ route('tamans.index') }}"
   class="{{ $linkClass }} {{ request()->routeIs('tamans.index', 'tamans.show') ? 'bg-lime-100 text-green-700' : 'text-gray-700' }}">
    Jelajahi Taman
</a>
<a href="{{ route('tamans.map') }}"
   class="{{ $linkClass }} {{ request()->routeIs('tamans.map') ? 'bg-lime-100 text-green-700' : 'text-gray-700' }}">
    Peta Taman
</a>
<a href="{{ route('rth.index') }}"
   class="{{ $linkClass }} {{ request()->routeIs('rth.*') ? 'bg-lime-100 text-green-700' : 'text-gray-700' }}">
    Statistik RTH
</a>
