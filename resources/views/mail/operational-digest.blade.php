<x-mail::message>
# Ringkasan Operasional {{ config('app.name') }}

**Status:** {{ $summary['executive_status']['label'] }}  
**Tanggal:** {{ $summary['generated_at']->translatedFormat('d F Y H:i') }} WIB

{{ $summary['executive_status']['description'] }}

## Indikator Utama

| Indikator | Nilai |
|:--|--:|
| Taman terdaftar | {{ number_format($summary['total_taman']) }} |
| Penyelesaian operasional | {{ $summary['layanan_selesai_persen'] }}% |
| Kepuasan masyarakat | {{ $summary['survey_summary']['total'] > 0 ? number_format($summary['survey_summary']['average'], 1).'/5' : 'Belum ada data' }} |
| Aduan baru | {{ number_format($summary['aduan_baru']) }} |
| Jadwal terlambat | {{ $summary['jadwal_counts']['terlambat'] }} |

## Jadwal Operasional

- Hari ini: **{{ $summary['jadwal_counts']['hari_ini'] }}**
- Besok (H-1): **{{ $summary['jadwal_counts']['besok'] }}**
- Sedang diproses: **{{ $summary['jadwal_counts']['diproses'] }}**

@if ($summary['operationalAlertsTotal'] > 0)
## Perlu Perhatian

@foreach ($summary['operationalAlerts'] as $alert)
- **{{ $alert['label'] }}** ({{ $alert['count'] }}) — {{ $alert['description'] }}
@endforeach
@else
Semua indikator operasional dalam kondisi normal.
@endif

<x-mail::button :url="route('admin.dashboard')">
Buka Dashboard
</x-mail::button>

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
