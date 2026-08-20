<?php

namespace App\Http\Requests\Admin;

use App\Models\AduanMasyarakat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAduanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('aduan_masyarakat')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(AduanMasyarakat::STATUS)],
            'catatan_admin' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
