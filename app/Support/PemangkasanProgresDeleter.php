<?php

namespace App\Support;

use App\Models\Pemangkasan;
use App\Models\PemangkasanProgres;
use Illuminate\Support\Facades\Storage;

class PemangkasanProgresDeleter
{
    public function delete(Pemangkasan $permohonan, PemangkasanProgres $entry): Pemangkasan
    {
        foreach (PemangkasanProgres::fotoFieldKeys() as $field) {
            $path = $entry->{$field};

            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

        $entry->delete();

        $permohonan->refresh();

        if ($permohonan->progres()->count() === 0 && $permohonan->status === 'Diproses') {
            $permohonan->update([
                'status' => 'Rencana',
                'tanggal_penyelesaian' => null,
            ]);
        }

        PemangkasanSchedule::recalculate($permohonan);

        return $permohonan->fresh(['progres']);
    }
}
