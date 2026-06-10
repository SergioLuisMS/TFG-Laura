<?php

namespace App\Services;

use App\Models\SesionEstudio;

/**
 * Centralizo aqui la logica de negocio de las sesiones de estudio.
 * Tanto el guardado final como el sistema de pulsos pasan por este servicio
 * para que el limite de segundos se aplique en un unico lugar.
 */
class SalaService
{
    /**
     * Maximo de segundos que puede acumular una sesion en un dia (24 horas).
     * Lo recorto en lugar de rechazar la peticion para que el usuario no pierda
     * el trabajo de una sesion muy larga por un error de desbordamiento.
     */
    private const MAX_SEGUNDOS = 86400;

    /**
     * Guardo o actualizo la sesion de estudio del usuario para el dia de hoy.
     *
     * El tiempo que envia el cliente es la fuente de verdad; sobreescribo
     * el valor que los pulsos habian acumulado en la BD porque el cronometro
     * del navegador es mas preciso que los incrementos de 30s.
     */
    public function guardarSesion(int $userId, string $sala, int $segundos): SesionEstudio
    {
        $segundosLimpios = min($segundos, self::MAX_SEGUNDOS);
        $hoy = now()->toDateString();
        $salaLimpia = strtolower(trim($sala));

        $registro = SesionEstudio::where('user_id', $userId)
            ->where('sala', $salaLimpia)
            ->whereDate('fecha_inicio', $hoy)
            ->first();

        if ($registro) {
            $registro->update(['segundos' => $segundosLimpios]);
            return $registro;
        }

        return SesionEstudio::create([
            'user_id'      => $userId,
            'sala'         => $salaLimpia,
            'fecha_inicio' => now(),
            'segundos'     => $segundosLimpios,
        ]);
    }

    /**
     * Registro un pulso de actividad sumando 30 segundos a la sesion del dia.
     *
     * Este metodo es el respaldo para cuando el usuario cierra la pagina sin
     * pulsar "Terminar". Los pulsos llegan cada 60s desde el cliente, pero
     * solo sumo 30s para no doblar el tiempo en caso de latencia.
     *
     * Respeto el mismo techo de MAX_SEGUNDOS para evitar desbordamientos
     * aunque el usuario deje la pestaña abierta mucho tiempo.
     */
    public function registrarPulso(int $userId, string $sala): void
    {
        $hoy = now()->toDateString();

        $registro = SesionEstudio::where('user_id', $userId)
            ->where('sala', $sala)
            ->whereDate('fecha_inicio', $hoy)
            ->first();

        if ($registro) {
            $nuevoTiempo = min($registro->segundos + 30, self::MAX_SEGUNDOS);
            $registro->update(['segundos' => $nuevoTiempo]);
        } else {
            SesionEstudio::create([
                'user_id'      => $userId,
                'sala'         => $sala,
                'fecha_inicio' => now(),
                'segundos'     => 30,
            ]);
        }
    }
}
