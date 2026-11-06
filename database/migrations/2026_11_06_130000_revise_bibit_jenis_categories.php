<?php

use App\Models\Bibit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('bibits')->select('id', 'jenis')->orderBy('id')->get() as $row) {
            $canonical = Bibit::resolveJenis($row->jenis);

            if ($canonical !== null && $canonical !== $row->jenis) {
                DB::table('bibits')->where('id', $row->id)->update(['jenis' => $canonical]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
