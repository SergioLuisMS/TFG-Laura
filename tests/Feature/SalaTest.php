<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebo el sistema de salas de estudio: acceso, guardado de sesion y pulsos.
 * Pongo especial atencion en las validaciones de seguridad del tiempo enviado por el cliente.
 */
class SalaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifico que un usuario autenticado puede acceder a una sala.
     */
    public function test_usuario_puede_acceder_a_sala(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/salas/biblioteca');

        $response->assertStatus(200);
    }

    /**
     * Verifico que una sala inexistente devuelve 404.
     */
    public function test_sala_inexistente_devuelve_404(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/salas/sala-fantasma');

        $response->assertStatus(404);
    }

    /**
     * Verifico que guardar una sesion con tiempo valido funciona.
     */
    public function test_guardar_sesion_con_tiempo_valido(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/salas/guardar', [
            'sala'     => 'biblioteca',
            'segundos' => 3600,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sesiones_estudio', [
            'user_id'  => $user->id,
            'sala'     => 'biblioteca',
            'segundos' => 3600,
        ]);
    }

    /**
     * Verifico que no se pueden guardar mas de 86400 segundos (24h) en una sesion.
     * Este es el Bug #2: tiempo manipulable por el cliente.
     */
    public function test_guardar_sesion_rechaza_tiempo_excesivo(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/salas/guardar', [
            'sala'     => 'biblioteca',
            'segundos' => 999999,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Verifico que no se pueden guardar segundos negativos.
     */
    public function test_guardar_sesion_rechaza_segundos_negativos(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/salas/guardar', [
            'sala'     => 'biblioteca',
            'segundos' => -100,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Verifico que no se puede guardar una sesion para una sala que no existe.
     */
    public function test_guardar_sesion_rechaza_sala_invalida(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/salas/guardar', [
            'sala'     => 'sala-trampa',
            'segundos' => 1800,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Verifico que el pulso automatico funciona y crea un registro en la BD.
     */
    public function test_registrar_pulso_crea_sesion(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/salas/registrar-pulso', [
            'sala' => 'biblioteca',
        ]);

        $response->assertJson(['status' => 'ok']);
        $this->assertDatabaseHas('sesiones_estudio', [
            'user_id'  => $user->id,
            'sala'     => 'biblioteca',
            'segundos' => 30,
        ]);
    }

    /**
     * Verifico que un invitado no puede acceder a las salas.
     */
    public function test_invitado_no_puede_acceder_a_sala(): void
    {
        $response = $this->get('/salas/biblioteca');

        $response->assertRedirect('/login');
    }
}
