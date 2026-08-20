<?php



namespace App\Http\Requests;



use App\Rules\Honeypot;

use Illuminate\Foundation\Http\FormRequest;



class CheckAduanStatusRequest extends FormRequest

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

            'nomor_aduan' => [

                'required',

                'string',

                'regex:/^ADU-\d{8}-\d{4}$/i',

            ],

            'kontak_verifikasi' => [

                'required',

                'string',

                'regex:/^\d{4}$/',

            ],

            '_website' => ['nullable', 'string', new Honeypot],

        ];

    }



    /**

     * @return array<string, string>

     */

    public function attributes(): array

    {

        return [

            'nomor_aduan' => 'nomor aduan',

            'kontak_verifikasi' => '4 digit terakhir nomor HP',

        ];

    }



    protected function prepareForValidation(): void

    {

        if ($this->filled('nomor_aduan')) {

            $this->merge([

                'nomor_aduan' => strtoupper(trim((string) $this->input('nomor_aduan'))),

            ]);

        }



        if ($this->filled('kontak_verifikasi')) {

            $this->merge([

                'kontak_verifikasi' => preg_replace('/\D/', '', (string) $this->input('kontak_verifikasi')),

            ]);

        }

    }

}

