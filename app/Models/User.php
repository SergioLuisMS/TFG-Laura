<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Implemento MustVerifyEmail para que Laravel gestione el flujo de verificacion
 * de correo automaticamente (Seguridad #27).
 *
 * Para que la verificacion funcione hay que configurar MAIL_MAILER en el .env.
 * En desarrollo se puede usar MAIL_MAILER=log y el enlace aparecera en storage/logs/laravel.log.
 * Para activar la barrera de acceso, anade ->middleware('verified') al grupo de rutas protegidas.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_base',
        'avatar_boca',
        'avatar_ojos',
        'avatar_complemento',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Relacion con los libros del usuario (su estanteria personal).
     * Incluye los campos del pivote: estado y puntuacion.
     */
    public function libros(): BelongsToMany
    {
        return $this->belongsToMany(Libro::class, 'book_user', 'user_id', 'book_id')
            ->withPivot('estado', 'puntuacion')
            ->withTimestamps();
    }

    /**
     * Solicitudes de amistad que yo he enviado (yo soy usuario_id).
     */
    public function amigosEnviados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'amigos', 'usuario_id', 'amigo_id')
            ->withPivot('estado')
            ->withTimestamps();
    }

    /**
     * Solicitudes de amistad que he recibido (yo soy amigo_id).
     */
    public function amigosRecibidos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'amigos', 'amigo_id', 'usuario_id')
            ->withPivot('estado')
            ->withTimestamps();
    }

    /**
     * Devuelvo todos mis amigos con amistad aceptada, sin importar quien envio la solicitud.
     */
    public function misAmigos()
    {
        $enviados  = $this->amigosEnviados()->wherePivot('estado', 'aceptada')->get();
        $recibidos = $this->amigosRecibidos()->wherePivot('estado', 'aceptada')->get();

        return $enviados->merge($recibidos);
    }

    /**
     * Solo las amistades que yo inicie y que ya fueron aceptadas (un subconjunto de misAmigos).
     */
    public function amigos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'amigos', 'usuario_id', 'amigo_id')
            ->wherePivot('estado', 'aceptada')
            ->withPivot('estado');
    }
}
