<?php

namespace App\Support;

use App\Models\BibitStokBuktiFoto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BibitStokBuktiFotoStorage
{
    /**
     * @param  list<UploadedFile>  $files
     */
    public function storeMany(Model $owner, array $files, string $directory): void
    {
        $urutan = (int) $owner->buktiFotos()->max('urutan');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $urutan++;
            $owner->buktiFotos()->create([
                'path' => $file->store($directory, 'public'),
                'urutan' => $urutan,
            ]);
        }
    }

    public function deleteAll(Model $owner): void
    {
        foreach ($owner->buktiFotos as $foto) {
            Storage::disk('public')->delete($foto->path);
            $foto->delete();
        }
    }

    public function deleteRecord(BibitStokBuktiFoto $foto): void
    {
        Storage::disk('public')->delete($foto->path);
        $foto->delete();
    }
}
