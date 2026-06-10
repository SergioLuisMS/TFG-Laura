<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Anadio un indice unico compuesto a la tabla amigos para reforzar a nivel de BD
 * que no puede existir la misma pareja (usuario_id, amigo_id) dos veces.
 *
 * El controlador ya comprueba ambas direcciones antes de insertar, pero este indice
 * actua como segunda linea de defensa ante condiciones de carrera o bugs futuros.
 *
 * Antes de crear el indice, elimino cualquier duplicado que pudiera existir
 * para que la migracion no falle en instalaciones con datos previos.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Elimino registros duplicados manteniendo el de menor id en cada pareja
        $duplicados = DB::table('amigos as a')
            ->join('amigos as b', function ($join) {
                $join->on('a.usuario_id', '=', 'b.usuario_id')
                     ->on('a.amigo_id', '=', 'b.amigo_id')
                     ->where('a.id', '<', 'b.id');
            })
            ->select('b.id')
            ->pluck('id');

        if ($duplicados->isNotEmpty()) {
            DB::table('amigos')->whereIn('id', $duplicados)->delete();
        }

        Schema::table('amigos', function (Blueprint $table) {
            $table->unique(['usuario_id', 'amigo_id'], 'amigos_pareja_unique');
        });
    }

    public function down(): void
    {
        Schema::table('amigos', function (Blueprint $table) {
            $table->dropUnique('amigos_pareja_unique');
        });
    }
};
