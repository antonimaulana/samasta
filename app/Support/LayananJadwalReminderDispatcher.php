<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\LayananJadwalNotification;

class LayananJadwalReminderDispatcher
{
    public function dispatch(): int
    {
        $recipients = User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_OPERATOR])
            ->get();

        if ($recipients->isEmpty()) {
            return 0;
        }

        $reminders = [
            [
                'category' => 'hari_ini',
                'count' => JadwalLayananQuery::hariIni()->count(),
                'title' => 'Jadwal Operasional Hari Ini',
                'message' => '%d operasional dijadwalkan hari ini.',
                'url' => route('admin.pemangkasans.index', ['view' => 'hari_ini']),
            ],
            [
                'category' => 'besok',
                'count' => JadwalLayananQuery::besok()->count(),
                'title' => 'Jadwal Operasional Besok',
                'message' => '%d operasional dijadwalkan besok (H-1).',
                'url' => route('admin.pemangkasans.index', ['view' => 'besok']),
            ],
            [
                'category' => 'terlambat',
                'count' => JadwalLayananQuery::terlambat()->count(),
                'title' => 'Jadwal Operasional Terlambat',
                'message' => '%d operasional melewati jadwal pelaksanaan.',
                'url' => route('admin.pemangkasans.index', ['view' => 'terlambat']),
            ],
        ];

        $sent = 0;

        foreach ($recipients as $user) {
            foreach ($reminders as $reminder) {
                if ($reminder['count'] === 0) {
                    continue;
                }

                $alreadySent = $user->notifications()
                    ->where('type', LayananJadwalNotification::class)
                    ->whereDate('created_at', today())
                    ->where('data->category', $reminder['category'])
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $user->notify(new LayananJadwalNotification(
                    category: $reminder['category'],
                    title: $reminder['title'],
                    message: sprintf($reminder['message'], $reminder['count']),
                    url: $reminder['url'],
                    count: $reminder['count'],
                ));

                $sent++;
            }
        }

        return $sent;
    }
}
