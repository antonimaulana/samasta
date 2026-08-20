<?php

namespace Database\Seeders;

use App\Models\DpaDocumentTemplate;
use App\Support\DpaMonitoring;
use Illuminate\Database\Seeder;

class DpaDocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DpaMonitoring::DOKUMEN_PER_TAHAP as $tahap => $documents) {
            foreach ($documents as $document) {
                DpaDocumentTemplate::query()->updateOrCreate(
                    ['kode' => $document['kode']],
                    [
                        'tahap' => $tahap,
                        'nama' => $document['label'],
                    ],
                );
            }
        }
    }
}
