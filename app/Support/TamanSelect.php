<?php

namespace App\Support;

use App\Models\Taman;

class TamanSelect
{
    /**
     * @param  iterable<int, Taman>  $tamans
     * @return list<array{value: string, label: string, search: string, html: string}>
     */
    public static function options(iterable $tamans): array
    {
        return collect($tamans)
            ->map(function (Taman $taman) {
                $label = $taman->nama_taman;

                if ($taman->kategori) {
                    $label .= ' ('.$taman->kategori.')';
                }

                if ($taman->alamat) {
                    $label .= ' — '.$taman->alamat;
                }

                return [
                    'value' => (string) $taman->id,
                    'label' => $label,
                    'search' => mb_strtolower(trim($taman->nama_taman.' '.$taman->kategori.' '.$taman->alamat)),
                    'kelurahan_id' => $taman->kelurahan_id ? (string) $taman->kelurahan_id : '',
                    'html' => '<span class="font-medium">'.e($taman->nama_taman).'</span>'
                        .($taman->kategori ? ' <span class="text-gray-500">('.e($taman->kategori).')</span>' : '')
                        .($taman->alamat ? '<br><span class="text-xs text-gray-500">'.e($taman->alamat).'</span>' : ''),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  list<array{value: string, label: string, search: string, html: string}>  $options
     */
    public static function selectedLabel(array $options, string|int|null $selectedId): string
    {
        if ($selectedId === null || $selectedId === '') {
            return '';
        }

        foreach ($options as $option) {
            if ($option['value'] === (string) $selectedId) {
                return $option['label'];
            }
        }

        return '';
    }
}
