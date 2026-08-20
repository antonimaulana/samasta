<?php

namespace App\Http\Requests;

use App\Models\AduanMasyarakat;
use App\Rules\Honeypot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAduanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'taman_id' => ['required', 'exists:tamans,id'],
            'jenis_aduan' => ['required', Rule::in(AduanMasyarakat::JENIS)],
            'deskripsi' => ['required', 'string', 'min:20', 'max:2000'],
            'foto' => ['required', 'image', 'max:4096'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'nama_pelapor' => ['required', 'string', 'max:100'],
            'kontak_pelapor' => ['required', 'string', 'min:9', 'max:50', 'regex:/^[0-9+\s\-]+$/'],
            '_website' => ['nullable', 'string', new Honeypot],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'taman_id' => 'taman terkait',
            'jenis_aduan' => 'jenis aduan',
            'foto' => 'foto bukti',
            'latitude' => 'lokasi GPS',
            'longitude' => 'lokasi GPS',
            'nama_pelapor' => 'nama pelapor',
            'kontak_pelapor' => 'nomor HP / WhatsApp',
        ];
    }
}
