<?php



namespace App\Console\Commands;



use App\Support\LayananJadwalReminderDispatcher;

use Illuminate\Console\Command;



class SendLayananJadwalReminders extends Command

{

    protected $signature = 'layanan:send-reminders';



    protected $description = 'Kirim notifikasi in-app untuk jadwal layanan hari ini, besok, dan terlambat';



    public function handle(LayananJadwalReminderDispatcher $dispatcher): int

    {

        $sent = $dispatcher->dispatch();



        $this->info("Notifikasi jadwal layanan terkirim: {$sent}.");



        return self::SUCCESS;

    }

}

