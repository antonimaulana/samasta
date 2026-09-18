<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->convertDateColumnToDateTime('pemeliharaan_tamans', 'tanggal', [
            'drop_indexes' => ['pemeliharaan_tamans_tanggal_tim_index'],
            'recreate_indexes' => [['tanggal', 'tim']],
        ]);

        if ($this->indexExists('pemangkasan_progres', 'pemangkasan_progres_pemangkasan_id_tanggal_unique')) {
            if (! $this->indexExists('pemangkasan_progres', 'pemangkasan_progres_pemangkasan_id_index')) {
                Schema::table('pemangkasan_progres', function (Blueprint $table) {
                    $table->index('pemangkasan_id', 'pemangkasan_progres_pemangkasan_id_index');
                });
            }

            Schema::table('pemangkasan_progres', function (Blueprint $table) {
                $table->dropUnique(['pemangkasan_id', 'tanggal']);
            });
        }

        $this->convertDateColumnToDateTime('pemangkasan_progres', 'tanggal');
    }

    public function down(): void
    {
        $this->convertDateTimeColumnToDate('pemangkasan_progres', 'tanggal');

        Schema::table('pemangkasan_progres', function (Blueprint $table) {
            $table->unique(['pemangkasan_id', 'tanggal']);
        });

        $this->convertDateTimeColumnToDate('pemeliharaan_tamans', 'tanggal', [
            'drop_indexes' => ['pemeliharaan_tamans_tanggal_tim_index'],
            'recreate_indexes' => [['tanggal', 'tim']],
        ]);
    }

    /**
     * @param  list<string>  $dropIndexes
     * @param  list<list<string>>  $recreateIndexes
     */
    private function convertDateColumnToDateTime(
        string $table,
        string $column,
        array $options = [],
    ): void {
        $driver = Schema::getConnection()->getDriverName();
        $tempColumn = $column.'_datetime_tmp';

        if ($driver === 'sqlite') {
            $columns = Schema::getColumnListing($table);

            if (in_array($tempColumn, $columns) && in_array($column, $columns)) {
                $this->finishSqliteDateTimeConversion($table, $column, $tempColumn, $options);

                return;
            }

            if (! in_array($column, $columns) || in_array($tempColumn, $columns)) {
                return;
            }

            foreach ($options['drop_indexes'] ?? [] as $index) {
                DB::statement("DROP INDEX IF EXISTS {$index}");
            }

            Schema::table($table, function (Blueprint $blueprint) use ($tempColumn) {
                $blueprint->dateTime($tempColumn)->nullable();
            });

            DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $column, $tempColumn) {
                foreach ($rows as $row) {
                    DB::table($table)->where('id', $row->id)->update([
                        $tempColumn => substr((string) $row->{$column}, 0, 10).' 08:00:00',
                    ]);
                }
            });

            $this->finishSqliteDateTimeConversion($table, $column, $tempColumn, $options);

            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE {$table} MODIFY {$column} DATETIME NOT NULL");

            return;
        }

        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE TIMESTAMP USING {$column}::timestamp");
    }

    /**
     * @param  list<string>  $dropIndexes
     * @param  list<list<string>>  $recreateIndexes
     */
    private function convertDateTimeColumnToDate(
        string $table,
        string $column,
        array $options = [],
    ): void {
        $driver = Schema::getConnection()->getDriverName();
        $tempColumn = $column.'_date_tmp';

        if ($driver === 'sqlite') {
            foreach ($options['drop_indexes'] ?? [] as $index) {
                DB::statement("DROP INDEX IF EXISTS {$index}");
            }

            Schema::table($table, function (Blueprint $blueprint) use ($tempColumn) {
                $blueprint->date($tempColumn)->nullable();
            });

            DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $column, $tempColumn) {
                foreach ($rows as $row) {
                    DB::table($table)->where('id', $row->id)->update([
                        $tempColumn => substr((string) $row->{$column}, 0, 10),
                    ]);
                }
            });

            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropColumn($column);
            });

            DB::statement("ALTER TABLE {$table} RENAME COLUMN {$tempColumn} TO {$column}");

            foreach ($options['recreate_indexes'] ?? [] as $columns) {
                Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                    $blueprint->index($columns);
                });
            }

            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE {$table} MODIFY {$column} DATE NOT NULL");

            return;
        }

        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE DATE USING {$column}::date");
    }

    /**
     * @param  list<string>  $dropIndexes
     * @param  list<list<string>>  $recreateIndexes
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $database = Schema::getConnection()->getDatabaseName();
            $result = DB::selectOne(
                'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
                [$database, $table, $indexName]
            );

            return $result !== null;
        }

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list({$table})");

            foreach ($rows as $row) {
                if (($row->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    private function finishSqliteDateTimeConversion(
        string $table,
        string $column,
        string $tempColumn,
        array $options = [],
    ): void {
        DB::table($table)
            ->whereNull($tempColumn)
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($table, $column, $tempColumn) {
                foreach ($rows as $row) {
                    DB::table($table)->where('id', $row->id)->update([
                        $tempColumn => substr((string) $row->{$column}, 0, 10).' 08:00:00',
                    ]);
                }
            });

        Schema::table($table, function (Blueprint $blueprint) use ($column) {
            $blueprint->dropColumn($column);
        });

        DB::statement("ALTER TABLE {$table} RENAME COLUMN {$tempColumn} TO {$column}");

        foreach ($options['recreate_indexes'] ?? [] as $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                $blueprint->index($columns);
            });
        }
    }
};
