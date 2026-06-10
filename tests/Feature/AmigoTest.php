<?php

namespace Tests\Feature;

use App\Models\Amigo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebo el sistema de amistades: enviar, aceptar, rechazar y eliminar.
 * Tambien verifico que las protecciones de acceso al perfil funcionan.
 */
class AmigoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifico que un usuario puede enviar una solicitud de amistad.
     */
    public function test_usuario_puede_enviar_solicitud(): void
    {
        $remitente    = User::factory()->create();
        $destinatario = User::factory()->create();

        $this->actingAs($remitente);

        $response = $this->post("/amigos/enviar/{$destinatario->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('amigos', [
            'usuario_id' => $remitente->id,
            'amigo_id'   => $destinatario->id,
            'estado'     => 'pendiente',
        ]);
    }

    /**
     * Verifico que no se pueden crear solicitudes duplicadas.
     * Si A ya envio a B, A no debe poder volver a enviar.
     */
    public function test_no_se_puede_enviar_solicitud_duplicada(): void
    {
        $remitente    = User::factory()->create();
        $destinatario = User::factory()->create();

        Amigo::create([
            'usuario_id' => $remitente->id,
            'amigo_id'   => $destinatario->id,
            'estado'     => 'pendiente',
        ]);

        $this->actingAs($remitente);

        $response = $this->post("/amigos/enviar/{$destinatario->id}");

        $response->assertRedirect();

        // Solo debe existir un registro, no dos
        $this->assertDatabaseCount('amigos', 1);
    }

    /**
     * Verifico que tampoco se puede enviar solicitud en direccion inversa si ya existe relacion.
     * Bug #4: B no debe poder enviar a A si A ya envio a B.
     */
    public function test_no_se_puede_enviar_solicitud_en_direccion_inversa(): void
    {
        $usuarioA = User::factory()->create();
        $usuarioB = User::factory()->create();

        // A ya envio solicitud a B
        Amigo::create([
            'usuario_id' => $usuarioA->id,
            'amigo_id'   => $usuarioB->id,
            'estado'     => 'pendiente',
        ]);

        // B intenta enviar solicitud a A (deberia ser rechazado)
        $this->actingAs($usuarioB);

        $this->post("/amigos/enviar/{$usuarioA->id}");

        // Solo debe existir el registro original, no uno nuevo en la otra direccion
        $this->assertDatabaseCount('amigos', 1);
    }

    /**
     * Verifico que aceptar una solicitud cambia el estado a 'aceptada'.
     */
    public function test_usuario_puede_aceptar_solicitud(): void
    {
        $remitente    = User::factory()->create();
        $destinatario = User::factory()->create();

        Amigo::create([
            'usuario_id' => $remitente->id,
            'amigo_id'   => $destinatario->id,
            'estado'     => 'pendiente',
        ]);

        $this->actingAs($destinatario);

        $this->post("/amigos/aceptar/{$remitente->id}");

        $this->assertDatabaseHas('amigos', [
            'usuario_id' => $remitente->id,
            'amigo_id'   => $destinatario->id,
            'estado'     => 'aceptada',
        ]);
    }

    /**
     * Verifico que rechazar una solicitud elimina el registro de la BD.
     */
    public function test_rechazar_solicitud_elimina_el_registro(): void
    {
        $remitente    = User::factory()->create();
        $destinatario = User::factory()->create();

        Amigo::create([
            'usuario_id' => $remitente->id,
            'amigo_id'   => $destinatario->id,
            'estado'     => 'pendiente',
        ]);

        $this->actingAs($destinatario);

        $this->post("/amigos/rechazar/{$remitente->id}");

        $this->assertDatabaseMissing('amigos', [
            'usuario_id' => $remitente->id,
            'amigo_id'   => $destinatario->id,
        ]);
    }

    /**
     * Verifico que un usuario no puede visitar el perfil de alguien que no es su amigo.
     */
    public function test_no_puede_visitar_perfil_sin_amistad(): void
    {
        $usuarioA = User::factory()->create();
        $usuarioB = User::factory()->create();

        $this->actingAs($usuarioA);

        $response = $this->get("/visitar-perfil/{$usuarioB->id}");

        $response->assertStatus(403);
    }

    /**
     * Verifico que con amistad aceptada si se puede visitar el perfil.
     */
    public function test_puede_visitar_perfil_con_amistad_aceptada(): void
    {
        $usuarioA = User::factory()->create();
        $usuarioB = User::factory()->create();

        Amigo::create([
            'usuario_id' => $usuarioA->id,
            'amigo_id'   => $usuarioB->id,
            'estado'     => 'aceptada',
        ]);

        $this->actingAs($usuarioA);

        $response = $this->get("/visitar-perfil/{$usuarioB->id}");

        $response->assertStatus(200);
    }
}
