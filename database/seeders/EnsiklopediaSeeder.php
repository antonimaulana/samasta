<?php

namespace Database\Seeders;

use App\Models\EnsiklopediaArtikel;
use App\Models\EnsiklopediaKategori;
use App\Support\Ensiklopedia;
use Illuminate\Database\Seeder;

class EnsiklopediaSeeder extends Seeder
{
    public function run(): void
    {
        if (EnsiklopediaKategori::exists()) {
            return;
        }

        foreach (Ensiklopedia::kategori() as $index => $data) {
            $kategori = EnsiklopediaKategori::create([
                'nama' => $data['nama'],
                'slug' => $data['slug'],
                'icon' => $data['icon'],
                'deskripsi' => $data['deskripsi'],
                'urutan' => $index,
            ]);

            foreach ($data['artikel'] as $artikelIndex => $artikel) {
                EnsiklopediaArtikel::create([
                    'ensiklopedia_kategori_id' => $kategori->id,
                    'judul' => $artikel['judul'],
                    'slug' => $artikel['slug'],
                    'icon' => $artikel['icon'],
                    'ringkas' => $artikel['ringkas'],
                    'konten' => $artikel['konten'],
                    'urutan' => $artikelIndex,
                    'is_published' => true,
                ]);
            }
        }
    }
}
