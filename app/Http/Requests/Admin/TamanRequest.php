<?php

namespace App\Http\Requests\Admin;

use App\Models\Taman;
use App\Support\OperatorWilayahScope;
use App\Support\TamanWilayahAssigner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
abstract class TamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $items = collect($this->input('fasilitas_items', []))
            ->map(function (array $item): array {
                $nama = trim((string) ($item['nama'] ?? ''));
                if ($nama === Taman::FASILITAS_CUSTOM_VALUE) {
                    $nama = trim((string) ($item['nama_custom'] ?? ''));
                }

                return [
                    'nama' => $nama,
                    'kondisi' => trim((string) ($item['kondisi'] ?? '')),
                ];
            })
            ->filter(fn (array $item) => filled($item['nama']))
            ->unique(fn (array $item) => mb_strtolower($item['nama']))
            ->values()
            ->all();

        $this->merge([
            'fasilitas_items' => $items === [] ? null : $items,
        ]);

        if (! $this->filled('data_verified_at')) {
            $this->merge([
                'data_verified_at' => now()->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            ]);
        }

        if ($this->has('luasan') && ! filled($this->input('luasan'))) {
            $this->merge(['luasan' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $currentYear = (int) date('Y');

        return [
            'nama_taman' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(Taman::KATEGORI)],
            'kelurahan_id' => ['nullable', 'exists:kelurahans,id'],
            'luasan' => ['nullable', 'integer', 'min:0'],
            'alamat' => ['nullable', 'string'],
            'latitude' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'fasilitas_items' => ['nullable', 'array'],
            'fasilitas_items.*.nama' => ['required', 'string', 'max:'.Taman::FASILITAS_NAMA_MAX_LENGTH],
            'fasilitas_items.*.nama_custom' => ['nullable', 'string', 'max:'.Taman::FASILITAS_NAMA_MAX_LENGTH],
            'fasilitas_items.*.kondisi' => ['required', Rule::in(Taman::FASILITAS_KONDISI)],
            'tahun_pembangunan' => ['nullable', 'integer', 'min:1950', 'max:'.$currentYear],
            'nilai_pembangunan' => ['nullable', 'integer', 'min:0'],
            'kontraktor' => ['nullable', 'string', 'max:255'],
            'konsultan_perencana' => ['nullable', 'string', 'max:255'],
            'data_verified_at' => ['nullable', 'date'],
            'foto' => ['nullable', 'image', 'max:'.Taman::GALLERY_MAX_SIZE_KB],
            'fotos' => ['nullable', 'array'],
            'fotos.*' => ['image', 'max:'.Taman::GALLERY_MAX_SIZE_KB],
            'hapus_fotos' => ['nullable', 'array'],
            'hapus_fotos.*' => ['integer', 'exists:taman_images,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fotos.*.max' => 'Setiap foto maksimal '.Taman::GALLERY_MAX_SIZE_KB.' KB.',
            'fasilitas_items.*.kondisi.required' => 'Pilih kondisi untuk setiap fasilitas yang diisi.',
            'fasilitas_items.*.kondisi.in' => 'Kondisi fasilitas harus Baik, Rusak Ringan, atau Rusak Berat.',
            'fasilitas_items.*.nama.required' => 'Pilih fasilitas atau isi nama fasilitas.',
            'fasilitas_items.*.nama.max' => 'Nama fasilitas maksimal '.Taman::FASILITAS_NAMA_MAX_LENGTH.' karakter.',
            'fasilitas_items.*.nama_custom.max' => 'Nama fasilitas maksimal '.Taman::FASILITAS_NAMA_MAX_LENGTH.' karakter.',
            'data_verified_at.date' => 'Format waktu pemutakhiran tidak valid.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedTamanPayload(): array
    {
        $validated = $this->validated();
        $validated = array_merge($validated, Taman::applyDefaultCoordinates(
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
        ));
        $validated['luasan'] = $validated['luasan'] ?? 0;
        $validated['alamat'] = $validated['alamat'] ?? '';
        $validated['deskripsi'] = $validated['deskripsi'] ?? '';
        $validated['fasilitas'] = Taman::normalizeFasilitasArray($validated['fasilitas_items'] ?? []);
        $validated['data_verified_at'] = Carbon::parse(
            $validated['data_verified_at'] ?? now()->timezone(config('app.timezone'))->format('Y-m-d\TH:i'),
            config('app.timezone')
        );

        unset($validated['fotos'], $validated['hapus_fotos'], $validated['fasilitas_items']);

        $payload = app(TamanWilayahAssigner::class)->apply($validated);
        app(OperatorWilayahScope::class)->assertKelurahanAllowed($this->user(), $payload['kelurahan_id'] ?? null);

        return $payload;
    }
}
