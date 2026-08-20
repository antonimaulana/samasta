<?php

namespace App\Console\Commands;

use App\Support\OperationalDigestDispatcher;
use Illuminate\Console\Command;

class SendOperationalDigest extends Command
{
    protected $signature = 'operational:send-digest';

    protected $description = 'Kirim ringkasan operasional harian via email';

    public function handle(OperationalDigestDispatcher $dispatcher): int
    {
        if (! config('alerts.digest.enabled')) {
            $this->warn('Digest email nonaktif. Set OPERATIONAL_DIGEST_ENABLED=true di .env');

            return self::SUCCESS;
        }

        $sent = $dispatcher->dispatch();

        $this->info("Digest operasional terkirim ke {$sent} penerima.");

        return self::SUCCESS;
    }
}
