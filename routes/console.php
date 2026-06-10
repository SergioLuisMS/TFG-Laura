<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Limpieza diaria de mensajes del chat con más de 24 horas
// Para que funcione en producción hay que tener el cron del servidor apuntando a:
//   * * * * * php /ruta/al/proyecto/artisan schedule:run >> /dev/null 2>&1
Schedule::call(function () {
    DB::table('chat_mensajes')
        ->where('created_at', '<', now()->subDay())
        ->delete();
})->daily()->name('limpiar-chat');
