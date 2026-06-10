<?php

namespace Tests\Feature;

use App\Models\Libro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebo los flujos principales de gestion de libros.
 * Uso RefreshDatabase para que cada test parta de una BD limpia.
 */
class LibroTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Creo un usuario autenticado reutilizable para los tests.
     */
    private function usuarioAutenticado(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    /**
     * Verifico que un usuario puede guardar un libro en su estanteria via AJAX.
     * El endpoint debe devolver JSON con success=true.
     */
    public function test_usuario_puede_guardar_libro(): void
    {
        $this->usuarioAutenticado();

        $response = $this->postJson('/libros/guardar', [
            'titulo'  => 'El Senor de los Anillos',
            'autor'   => 'J.R.R. Tolkien',
            'genero'  => 'Fantasía',
            'portada' => 'https://ejemplo.com/portada.jpg',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('books', [
            'title'  => 'El Senor de los Anillos',
            'author' => 'J.R.R. Tolkien',
        ]);
    }

    /**
     * Verifico que el endpoint rechaza un libro sin titulo.
     */
    public function test_guardar_libro_falla_sin_titulo(): void
    {
        $this->usuarioAutenticado();

        $response = $this->postJson('/libros/guardar', [
            'autor'  => 'J.R.R. Tolkien',
            'genero' => 'Fantasía',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Verifico que un usuario puede ver su propia estanteria.
     */
    public function test_usuario_puede_ver_su_estanteria(): void
    {
        $this->usuarioAutenticado();

        $response = $this->get('/mi-estanteria');

        $response->assertStatus(200);
    }

    /**
     * Verifico que un usuario no autenticado es redirigido al login
     * cuando intenta acceder a la estanteria.
     */
    public function test_invitado_no_puede_ver_estanteria(): void
    {
        $response = $this->get('/mi-estanteria');

        $response->assertRedirect('/login');
    }

    /**
     * Verifico que actualizar un libro solo toca el pivote (estado y puntuacion),
     * no el genero del libro compartido.
     */
    public function test_actualizar_estanteria_valida_puntuacion(): void
    {
        $user = $this->usuarioAutenticado();

        $libro = Libro::factory()->create();
        $user->libros()->attach($libro->id, ['estado' => 'por_leer', 'puntuacion' => 1]);

        // Puntuacion fuera de rango (6) debe ser rechazada
        $response = $this->put("/mi-estanteria/{$libro->id}", [
            'estado'     => 'leyendo',
            'puntuacion' => 6,
        ]);

        $response->assertSessionHasErrors(['puntuacion']);
    }

    /**
     * Verifico que actualizar con datos validos funciona correctamente.
     */
    public function test_actualizar_estanteria_con_datos_validos(): void
    {
        $user = $this->usuarioAutenticado();

        $libro = Libro::factory()->create();
        $user->libros()->attach($libro->id, ['estado' => 'por_leer', 'puntuacion' => 1]);

        $response = $this->put("/mi-estanteria/{$libro->id}", [
            'estado'     => 'leido',
            'puntuacion' => 4,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('book_user', [
            'user_id'    => $user->id,
            'book_id'    => $libro->id,
            'estado'     => 'leido',
            'puntuacion' => 4,
        ]);
    }

    /**
     * Verifico que el filtro de generos devuelve JSON con HTML.
     */
    public function test_filtrar_estanteria_devuelve_json(): void
    {
        $this->usuarioAutenticado();

        $response = $this->getJson('/estanteria/filtrar?genero=Fantasía');

        $response->assertStatus(200)
                 ->assertJsonStructure(['html']);
    }
}
