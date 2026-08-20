<?php

namespace App\Support;

use App\Mail\OperationalDigestMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OperationalDigestDispatcher
{
    public function dispatch(): int
    {
        if (! config('alerts.digest.enabled')) {
            return 0;
        }

        $recipients = $this->recipients();

        if ($recipients === []) {
            return 0;
        }

        $summary = app(DashboardSummaryBuilder::class)->build();

        try {
            Mail::to($recipients)->send(new OperationalDigestMail($summary));
        } catch (\Throwable $exception) {
            Log::error('Gagal kirim digest operasional', [
                'error' => $exception->getMessage(),
            ]);

            return 0;
        }

        return count($recipients);
    }

    /**
     * @return list<string>
     */
    private function recipients(): array
    {
        $configured = collect(explode(',', (string) config('alerts.digest.recipients', '')))
            ->map(fn (string $email) => trim($email))
            ->filter(fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();

        if ($configured !== []) {
            return $configured;
        }

        return User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_OPERATOR])
            ->pluck('email')
            ->filter(fn (?string $email) => filled($email))
            ->unique()
            ->values()
            ->all();
    }
}
