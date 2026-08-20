<?php

namespace App\Http\Requests\Admin;

use App\Models\Taman;
use App\Support\OperatorWilayahScope;
use App\Support\TamanWilayahAssigner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class TamanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama_taman' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(Taman::KATEGORI)],
            'kelurahan_id' => ['nullable', 'exists:kelurahans,id'],
            'luasan' => ['required', 'integer', 'min:1'],
            'alamat' => ['required', 'string'],
            'latitude' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'fasilitas' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'fotos' => ['nullable', 'array'],
            'fotos.*' => ['image', 'max:2048'],
            'hapus_fotos' => ['nullable', 'array'],
            'hapus_fotos.*' => ['integer', 'exists:taman_images,id'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedTamanPayload(): array
    {
        $validated = $this->validated();
        $validated['fasilitas'] = $this->parseFasilitas($validated['fasilitas'] ?? null);

        unset($validated['fotos'], $validated['hapus_fotos']);

        $payload = app(TamanWilayahAssigner::class)->apply($validated);
        app(OperatorWilayahScope::class)->assertKelurahanAllowed($this->user(), $payload['kelurahan_id'] ?? null);

        return $payload;
    }

    /**
     * @return list<string>|null
     */
    protected function parseFasilitas(?string $fasilitas): ?array
    {
        if ($fasilitas === null || trim($fasilitas) === '') {
            return null;
        }

        $items = preg_split('/[\r\n,]+/', $fasilitas);

        return array_values(array_filter(array_map('trim', $items)));
    }
}
