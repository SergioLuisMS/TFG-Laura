# Desplegar en Railway (gratis) — Red de Patatas 🥔

Esta guía despliega el proyecto en [Railway](https://railway.app) con **MySQL**, sin cambiar
nada del funcionamiento. El `Dockerfile` ya se encarga de:

- Instalar las dependencias de PHP (Composer) y compilar los assets con Vite.
- Arrancar Apache en el puerto que asigna Railway.
- **Ejecutar las migraciones automáticamente** en cada despliegue.

> El `Dockerfile` anterior (que era para Render + PostgreSQL) se guardó como
> `Dockerfile.render.old`. El activo usa MySQL, que es lo que usa el proyecto.

---

## 0. Antes de empezar

Necesitas:
- Una cuenta de **GitHub** y otra de **Railway** (puedes entrar en Railway con GitHub).
- El proyecto subido a un repositorio de GitHub.

> ⚠️ **Importante sobre la carpeta raíz:** Railway tiene que ver el `Dockerfile`,
> `composer.json`, etc. en la **raíz** del repo (o del "Root Directory" que le indiques).
> En tu caso el proyecto Laravel está dentro de `2red-social-libros/2red-social-libros/`.
> - Si subes a GitHub **esa carpeta interna** (la que tiene `composer.json`) → todo va directo.
> - Si subes la carpeta **exterior**, en Railway tendrás que poner el *Root Directory* =
>   `2red-social-libros` (paso 3).

---

## 1. Subir el proyecto a GitHub

Desde la carpeta del proyecto (la que tiene `composer.json`):

```bash
git init
git add .
git commit -m "Preparar despliegue en Railway"
git branch -M main
git remote add origin https://github.com/TU-USUARIO/red-de-patatas.git
git push -u origin main
```

(`vendor/`, `node_modules/`, `.env` y `public/build` ya están en `.gitignore`: no se suben,
se reconstruyen en Railway.)

---

## 2. Crear el proyecto en Railway

1. Entra en https://railway.app → **New Project** → **Deploy from GitHub repo**.
2. Elige tu repositorio. Railway detectará el `Dockerfile` y `railway.json` automáticamente.

---

## 3. (Solo si subiste la carpeta exterior) Ajustar el Root Directory

En el servicio → **Settings** → **Source** → **Root Directory** → escribe `2red-social-libros`
→ guarda. Si subiste directamente la carpeta del proyecto, sáltate este paso.

---

## 4. Añadir la base de datos MySQL

1. En el proyecto de Railway → **New** → **Database** → **Add MySQL**.
2. Railway crea el servicio MySQL y sus variables (`MYSQLHOST`, `MYSQLPORT`, etc.).

---

## 5. Configurar las variables de entorno

En el servicio de la **app** (no en el de MySQL) → pestaña **Variables** → añade las del
archivo [`.env.railway.example`](.env.railway.example). Las imprescindibles:

| Variable | Valor |
|---|---|
| `APP_KEY` | Genera una (ver paso 6). Empieza por `base64:` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | El dominio público de Railway (paso 7) |
| `LOG_CHANNEL` | `stderr` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `DB_DATABASE` | `${{MySQL.MYSQLDATABASE}}` |
| `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
| `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `MAIL_MAILER` | `log`  ← **importante, si no el registro falla** |

> `${{MySQL.XXXX}}` son referencias automáticas al plugin de MySQL. Si tu servicio de base
> de datos no se llama exactamente `MySQL`, cambia ese nombre por el que tenga.
>
> **¿Por qué `MAIL_MAILER=log`?** Al registrarse, la app intenta enviar el correo de
> verificación de email. Sin un servidor de correo válido daría error 500. Con `log`, el
> correo se escribe en los logs y el registro funciona con normalidad.

---

## 6. Generar la `APP_KEY`

Opción rápida: copia la `APP_KEY` de tu `.env` local.

O genera una nueva en tu máquina:

```bash
docker compose exec laravel.test php artisan key:generate --show
```

Copia el valor completo (incluido `base64:`) y pégalo en la variable `APP_KEY` de Railway.

---

## 7. Generar el dominio público y fijar `APP_URL`

1. Servicio de la app → **Settings** → **Networking** → **Generate Domain**.
2. Copia la URL (algo como `https://red-de-patatas-production.up.railway.app`).
3. Pégala en la variable **`APP_URL`** y guarda. Railway hará un redeploy.

---

## 8. Comprobar

- Espera a que el deploy termine (verás los logs del `Dockerfile` y luego
  `[entrypoint] Ejecutando migraciones...`).
- Abre tu dominio. Debería cargar la home con estilos.
- Railway comprueba la salud en `/up` automáticamente.
- Prueba a registrarte y buscar un libro.

---

## Notas

- **Migraciones:** se ejecutan solas en cada arranque (`php artisan migrate --force`). Es
  idempotente; solo aplica lo que falte. No borra datos.
- **Recompilar assets:** no tienes que hacer nada; el `Dockerfile` los compila en cada deploy.
- **Tarea programada de limpieza de chat** (`routes/console.php`) necesita un cron. En el plan
  gratuito puedes ignorarla (el chat sigue funcionando; solo no se autolimpian los mensajes
  de +24h). Si la quieres, crea un **Cron** en Railway que ejecute `php artisan schedule:run`.
- **No subas tu `.env`**: ya está en `.gitignore`. Toda la config va por Variables de Railway.
- **Plan gratuito:** Railway da un crédito mensual. La app + MySQL entran de sobra para un TFG.
