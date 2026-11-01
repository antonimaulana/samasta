@php
    $isLate = \App\Support\PemangkasanSchedule::isLate($pemangkasan);
    $isActiveToday = \App\Support\PemangkasanSchedule::isActiveToday($pemangkasan);
    $adjustments = \App\Support\PemangkasanSchedule::jadwalAdjustmentLabels($pemangkasan);
@endphp

<p class="font-medium text-gray-900">{{ \App\Support\PemangkasanSchedule::labelRentang($pemangkasan) }}</p>
<p class="text-xs text-gray-500">{{ \App\Support\PemangkasanSchedule::progressSummary($pemangkasan) }}</p>
@foreach ($adjustments as $adjustment)
    <p class="text-xs font-semibold {{ $adjustment['class'] }}">{{ $adjustment['label'] }}</p>
@endforeach
@if ($isLate)
    <p class="text-xs font-medium text-red-600">Terlambat</p>
@elseif ($isActiveToday)
    <p class="text-xs font-medium text-emerald-700">Hari ini</p>
@elseif (\App\Support\PemangkasanSchedule::tanggalWithinSchedule($pemangkasan, today()->addDay()))
    <p class="text-xs font-medium text-blue-700">Besok</p>
@endif
