# CLAUDE.md — Red Social de Libros (TFG)

## Descripción del proyecto

Red social temática centrada en libros y lectura. Los usuarios crean un avatar de patata
personalizado, guardan libros en su estantería personal, los puntúan y marcan su estado
de lectura, cronometran sesiones de lectura en salas temáticas, y pueden añadir amigos
para ver sus estanterías.

Es un TFG de DAW. El código está escrito de forma personal y directa, con comentarios
en español y tono informal. Hay que mantener ese estilo si se toca algo.

---

## Stack tecnológico

- **Backend:** Laravel 13, PHP 8.3
- **Base de datos:** MySQL (via Laravel Sail / Docker)
- **Frontend:** Blade + CSS propio + Bootstrap + Vite
- **JS:** Vanilla JS modular (app.js, biblioteca.js, sala-interactiva.js, avatar-preview.js)
- **Autenticación:** Laravel Auth nativa (sesiones, middleware `auth`)
- **Assets:** Vite — los CSS de componentes se importan desde `resources/css/app.css`

---

## Estructura de directorios clave

```
app/
  Http/Controllers/
    AuthController.php       — login, registro, logout
    LibroController.php      — estantería, búsqueda, filtros, guardar libro
    PerfilController.php     — perfil, avatar, nombre, visitar amigo
    SalaController.php       — salas de concentración, guardar sesión, pulso
    AmigoController.php      — sistema de amigos (enviar, aceptar, rechazar, eliminar)
  Models/
    User.php                 — usuario con avatar en capas y relaciones
    Libro.php                — libro (tabla: books)
    Amigo.php                — solicitud de amistad (tabla: amigos)
    SesionEstudio.php        — sesión de lectura cronometrada (tabla: sesiones_estudio)

resources/
  views/
    plantilla/app.blade.php  — layout principal (nav, alertas, @yield content)
    menu.blade.php           — página de inicio / dashboard
    perfil.blade.php         — perfil del usuario con estadísticas
    perfil/editar-avatar.blade.php
    auth/login.blade.php
    auth/registro.blade.php
    libros/
      index.blade.php        — formulario de búsqueda de libros
      resultados.blade.php   — resultados con botón de añadir (AJAX)
      estanteria.blade.php   — estantería del usuario con filtros por género
      lista-libros-estanteria.blade.php  — parcial AJAX que usa filtrar()
    salas/
      index.blade.php        — mapa interactivo de la casa (image map)
      sala.blade.php         — sala de concentración con timer y chat
    amigos/
      perfil-amigo.blade.php — perfil de un amigo con su estantería
      tarjeta_usuario.blade.php — componente tarjeta (modos: buscar, gestion, solicitud_recibida)
    usuarios.blade.php       — lista de usuarios / amigos / solicitudes (3 pestañas)
    componentes/
      nav.blade.php          — barra de navegación
      form-avatar.blade.php  — selector de avatar en capas (radio buttons)
      alertas.blade.php      — sistema de alertas (sesión + AJAX)
  js/
    app.js                   — entry point; carga Bootstrap, SweetAlert2, módulos
    biblioteca.js            — AJAX para añadir libros y filtrar por género
    sala-interactiva.js      — timer, pulso automático, chat (localStorage), items
    avatar-preview.js        — preview en tiempo real del avatar al seleccionar opciones
  css/
    app.css                  — importa todos los CSS de componentes

database/migrations/         — migraciones en orden cronológico
routes/web.php               — todas las rutas del proyecto
```

---

## Base de datos — tablas y campos

### `users`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | string | único |
| email | string | único |
| password | string | hasheado |
| avatar_base | string | ej: `base/azulRelleno.png` |
| avatar_boca | string | ej: `boca/boca1.png` |
| avatar_ojos | string | ej: `ojos/ojos1.png` |
| avatar_complemento | string | ej: `complemento/complemento1.png` |
| timestamps | | |

### `books`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| title | string | |
| author | string | |
| genre | string | uno de los 9 géneros traducidos |
| cover_url | string | URL de la portada |
| user_id | bigint FK | quien lo guardó primero (no se usa mucho) |
| timestamps | | |

### `book_user` (pivot)
| Campo | Tipo | Notas |
|---|---|---|
| user_id | FK | |
| book_id | FK | |
| estado | enum | `por_leer`, `leyendo`, `leido` |
| puntuacion | integer | 1–5 (patatas 🥔) |
| timestamps | | |

### `amigos`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| usuario_id | FK → users | quien envía la solicitud |
| amigo_id | FK → users | quien la recibe |
| estado | string | `pendiente` o `aceptada` |
| timestamps | | |

### `sesiones_estudio`
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users | |
| sala | string | nombre de la sala (ej: `botica`) |
| segundos | integer | tiempo total en segundos |
| fecha_inicio | timestamp | se usa para agrupar por día |
| timestamps | | |

---

## Modelos y relaciones

### `User`
- `libros()` → BelongsToMany `Libro` via `book_user` — incluye pivot `estado`, `puntuacion`
- `amigosEnviados()` → BelongsToMany `User` via `amigos` (yo soy `usuario_id`)
- `amigosRecibidos()` → BelongsToMany `User` via `amigos` (yo soy `amigo_id`)
- `amigos()` → solo los `amigosEnviados` con estado `aceptada`
- `misAmigos()` → método helper que fusiona enviados + recibidos aceptados

### `Libro` (tabla: `books`)
- `usuarios()` → BelongsToMany `User` via `book_user`

### `Amigo` (tabla: `amigos`)
- `remitente()` → BelongsTo `User` (usuario_id)
- `destinatario()` → BelongsTo `User` (amigo_id)
- `sender()` → alias de `remitente()`, se usa en `AmigoController::index()` con `->with('sender')`

### `SesionEstudio` (tabla: `sesiones_estudio`)
- `user()` → BelongsTo `User`

---

## Rutas

```
GET  /                          home (pública, pasa $solicitudesPendientes si hay sesión)
GET  /login                     mostrarLogin
POST /login                     login (throttle: 5/min)
GET  /registro                  mostrarRegistro
POST /registro                  registrar
POST /logout                    logout
GET  /libros/buscar             buscar (pública)

-- Protegidas (middleware auth) --
GET  /perfil                    PerfilController@index
GET  /perfil/editar-avatar      PerfilController@editarAvatar
PUT  /perfil/actualizar-avatar  PerfilController@actualizarAvatar
POST /perfil/actualizar-nombre  PerfilController@actualizarNombre

GET  /mi-estanteria             LibroController@miEstanteria
GET  /estanteria/filtrar        LibroController@filtrar  ← devuelve JSON con HTML parcial
GET  /libros                    LibroController@inicio
POST /libros/guardar            LibroController@guardar  ← AJAX, devuelve JSON
DELETE /libros/{libro}          LibroController@eliminar
PUT  /mi-estanteria/{libro}     LibroController@actualizarEstanteria

GET  /salas                     SalaController@index
GET  /salas/{tipo}              SalaController@show
POST /salas/guardar             SalaController@guardar
POST /salas/registrar-pulso     SalaController@registrarPulso  ← AJAX

GET  /buscar-amigos             AmigoController@index
POST /amigos/enviar/{id}        AmigoController@enviarSolicitud
POST /amigos/aceptar/{id}       AmigoController@aceptarSolicitud
POST /amigos/rechazar/{id}      AmigoController@rechazarSolicitud
DELETE /amigos/eliminar/{id}    AmigoController@eliminarAmigo

GET  /visitar-perfil/{id}       PerfilController@visitarPerfil
```

---

## Funcionalidades principales

### Sistema de géneros
`App\Support\GeneroTraductor::traducir($texto)` convierte el género crudo que llega de los
resultados de búsqueda a uno de estos 9 géneros internos.
La lógica vive en `app/Support/GeneroTraductor.php`, no en el controlador.

```
Romántica | Fantasía | Policiaca | Terror | Ciencia Ficción | Aventura | Historia | Clásicos | Narrativa
```

El género se guarda en `books.genre` una sola vez cuando se crea el libro.
Si el libro ya existe (`firstOrCreate`), se reutiliza el registro con el género original.
El género **nunca** se sobreescribe al actualizar la estantería (solo cambian `estado` y `puntuacion` en el pivot).

La vista `estanteria.blade.php` muestra el género con un `@switch` que mapea cada
categoría a su emoji. El `@default` muestra `📚` + el valor de `$book->genre`.

### Filtro de géneros en estantería
- El JS de `biblioteca.js` escucha clics en `.btn-filtro` y hace fetch a `/estanteria/filtrar?genero=X`
- `LibroController::filtrar()` filtra `$user->libros()` por `genre` y renderiza la vista parcial
  `libros.lista-libros-estanteria` (archivo: `resources/views/libros/lista-libros-estanteria.blade.php`)
- Devuelve JSON: `{ html: '...' }` que el JS inyecta en `#contenedor-libros-ajax`

### Añadir libros (AJAX)
- `biblioteca.js::añadirLibroSinRecargar(btn)` lee `data-title`, `data-author`, `data-genre`, `data-cover` del botón
- Hace POST a `/libros/guardar` con JSON
- `LibroController::guardar()` hace `Libro::firstOrCreate()` y luego `DB::table('book_user')->updateOrInsert()`
- Devuelve JSON: `{ success: true, message: '...' }`

### Sistema de salas (cronómetro)
- El timer corre en cliente (`sala-interactiva.js`)
- Cada 60 segundos: pulso AJAX a `/salas/registrar-pulso` que incrementa `segundos += 30` en BD
  (sirve de backup si el usuario cierra la página)
- Al pulsar "TERMINAR": `finalizarSesion(event)` envía el tiempo real del cliente a `/salas/guardar`
  que **sobreescribe** el valor en BD con el tiempo exacto del cronómetro
- Un registro por usuario por sala por día (`whereDate('fecha_inicio', $hoy)`)

### Sistema de amigos
- La tabla `amigos` tiene `usuario_id` (quien envía) y `amigo_id` (quien recibe)
- Estados: `pendiente` → `aceptada` (no hay estado `rechazado`; rechazar borra el registro)
- Solo usuarios con amistad `aceptada` pueden verse el perfil mutuamente (`visitarPerfil`)
- `AmigoController::index()` reconstruye la lista de amigos manualmente desde la tabla
  (no usa las relaciones del modelo User directamente)
- El badge de notificaciones del menú principal viene de la ruta `/` que inyecta `$solicitudesPendientes`

### Avatar en capas
El avatar se compone de 4 imágenes superpuestas (z-index):
1. `base/` — color de la patata (fondo)
2. `ojos/` — ojos
3. `boca/` — boca (con `mix-blend-mode: multiply`)
4. `complemento/` — accesorio encima de todo

Los valores se guardan como rutas relativas: `base/azulRelleno.png`, `boca/boca1.png`, etc.
En las vistas se construye la URL completa con `asset('img/avatar/' . $user->avatar_base)`.

La whitelist de `PerfilController::actualizarAvatar()` usa exactamente esos mismos formatos
(`base/azulRelleno.png`, `boca/boca1.png`...) que coinciden con los `value` del formulario.

---

## Layout y CSS

El layout principal (`plantilla/app.blade.php`) incluye:
- `resources/css/app.css` y `resources/js/app.js` via Vite
- `<meta name="csrf-token">` y `<meta name="route-guardar-libro">` usados por el JS
- La clase del `<body>` es `esta-logueado` o `es-invitado` según sesión
- El atributo `data-sala` del body se usa en `app.js` para detectar en qué sala está el usuario
  e inicializar `sala-interactiva.js` con un pequeño delay

Los CSS por componente se cargan con `@vite(...)` en el `@section('meta')` de cada vista,
no todos en el layout global.

---

## JavaScript — módulos

### `app.js`
- Entry point de Vite
- Importa Bootstrap, SweetAlert2, `biblioteca.js`, `sala-interactiva.js`
- Lee `data-sala` del body: si es una sala conocida, llama `initSalaInteractiva(tipo, user)`
- Funciones globales: `toggleFormNombre()` (muestra/oculta form cambio nombre), `alertaInvitado()`
- Muestra alertas de validación de Laravel via SweetAlert2 buscando `#validation-alert`

### `biblioteca.js`
- `window.añadirLibroSinRecargar(btn)` — llamado desde `onclick` en la vista de resultados
- Listener de clics para `.btn-filtro` — filtro AJAX de géneros en estantería

### `sala-interactiva.js`
- `initSalaInteractiva(salaId, userPotato)` — exportada, llamada desde `app.js`
- Timer con `setInterval` cada 1 segundo, actualiza `#timer` y `#focus-bar`
- Pulso automático cada 60s al endpoint `/salas/registrar-pulso`
- Chat conectado al backend via polling cada 2.5s (`/chat/obtener`) y POST (`/chat/enviar`)
- Handler `beforeunload` con `keepalive:true` que guarda el tiempo si el usuario cierra la página sin "Terminar"
- Elementos interactivos específicos de la botica (botes, caldero)
- `window.finalizarSesion(event)` — intercepta el click en "TERMINAR", envía tiempo a `/salas/guardar`

### `avatar-preview.js`
- Escucha cambios en los radio buttons del formulario de avatar
- Actualiza las imágenes `#preview-base`, `#preview-boca`, `#preview-ojos`, `#preview-complemento`

---

## Notas importantes para trabajar en el proyecto

- **El modelo se llama `Libro` pero la tabla es `books`** — `protected $table = 'books'`
- **La relación en User se llama `libros()`** — usar siempre esa, nunca `books()`
- **El filtro de géneros usa una vista parcial** en `libros/lista-libros-estanteria.blade.php` —
  no está en `partials/`, ese era el sitio antiguo
- **El género nunca se toca en `actualizarEstanteria()`** — solo pivot (estado + puntuacion)
- **`guardar()` de SalaController sobreescribe**, no suma — el tiempo real es el del cronómetro cliente
- **Los valores de avatar incluyen la carpeta** (`base/azulRelleno.png`), no solo el nombre de archivo
- **`visitarPerfil()` lanza 403** si no hay amistad aceptada — comportamiento intencionado
- **El chat de las salas usa el backend** — `ChatController` con polling en el cliente. La tabla `chat_mensajes` almacena los mensajes por sala.
- **Enums para estados**: usar `EstadoLibro::PorLeer->value` y `EstadoAmistad::Aceptada->value` en lugar de strings directos.
- **Servicios**: `LibroService::guardarEnEstanteria()` y `SalaService::guardarSesion()` contienen la lógica de negocio.
- **Policy**: `UserPolicy::visitarPerfil()` registrada en `AppServiceProvider`. Usar `$this->authorize('visitarPerfil', $amigo)`.
- **Tests**: están en `tests/Feature/` — ejecutar con `php artisan test`.
- **Verificación de email**: User implementa `MustVerifyEmail`. Para activar la barrera añadir `verified` al middleware del grupo de rutas protegidas.
  en el navegador del usuario en esa sala
