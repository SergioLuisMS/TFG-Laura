<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebo los flujos de autenticacion: registro, login y logout.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifico que un nuevo usuario puede registrarse correctamente.
     */
    public function test_usuario_puede_registrarse(): void
    {
        $response = $this->post('/registro', [
            'name'               => 'Patata Test',
            'email'              => 'patata@test.com',
            'password'           => 'secreto123',
            'password_confirmation' => 'secreto123',
            'avatar_base'        => 'base/azulRelleno.png',
            'avatar_boca'        => 'boca/boca1.png',
            'avatar_ojos'        => 'ojos/ojos1.png',
            'avatar_complemento' => 'complemento/complemento1.png',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', ['email' => 'patata@test.com']);
    }

    /**
     * Verifico que el registro rechaza avatares fuera de la whitelist.
     * Esto impide que se inyecten rutas de imagen maliciosas en el avatar.
     */
    public function test_registro_rechaza_avatar_invalido(): void
    {
        $response = $this->post('/registro', [
            'name'               => 'Patata Trampa',
            'email'              => 'trampa@test.com',
            'password'           => 'secreto123',
            'password_confirmation' => 'secreto123',
            'avatar_base'        => '../../../etc/passwd',
            'avatar_boca'        => 'boca/boca1.png',
            'avatar_ojos'        => 'ojos/ojos1.png',
            'avatar_complemento' => 'complemento/complemento1.png',
        ]);

        $response->assertSessionHasErrors(['avatar_base']);
        $this->assertDatabaseMissing('users', ['email' => 'trampa@test.com']);
    }

    /**
     * Verifico que no se puede registrar dos cuentas con el mismo email.
     */
    public function test_registro_rechaza_email_duplicado(): void
    {
        User::factory()->create(['email' => 'existente@test.com']);

        $response = $this->post('/registro', [
            'name'               => 'Otra Patata',
            'email'              => 'existente@test.com',
            'password'           => 'secreto123',
            'password_confirmation' => 'secreto123',
            'avatar_base'        => 'base/azulRelleno.png',
            'avatar_boca'        => 'boca/boca1.png',
            'avatar_ojos'        => 'ojos/ojos1.png',
            'avatar_complemento' => 'complemento/complemento1.png',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Verifico que un usuario registrado puede hacer login.
     */
    public function test_usuario_puede_hacer_login(): void
    {
        User::factory()->create([
            'email'    => 'login@test.com',
            'password' => bcrypt('secreto123'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'login@test.com',
            'password' => 'secreto123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    /**
     * Verifico que credenciales incorrectas no permiten el acceso.
     */
    public function test_login_falla_con_credenciales_incorrectas(): void
    {
        User::factory()->create([
            'email'    => 'correcto@test.com',
            'password' => bcrypt('secreto123'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'correcto@test.com',
            'password' => 'contraseña-incorrecta',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Verifico que el logout cierra la sesion y redirige a inicio.
     */
    public function test_usuario_puede_hacer_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
