#!/usr/bin/env sh
# Entrypoint para Railway: ajusta el puerto, migra la base de datos y arranca Apache.
set -e

# Railway inyecta el puerto en $PORT. Si no existe (build/local), uso 8080 por defecto.
: "${PORT:=8080}"

echo "[entrypoint] Configurando Apache para escuchar en el puerto ${PORT}..."
# Reescribo el puerto tanto en ports.conf como en el VirtualHost.
sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Limpio cualquier cache de configuracion que se hubiera quedado del build.
echo "[entrypoint] Limpiando cache de configuracion..."
php artisan config:clear || true

# Ejecuto migraciones pendientes. Es idempotente: solo aplica las que falten.
# Si la base de datos no estuviera conectada, aviso pero arranco igual para poder
# ver el error en los logs de Railway en vez de entrar en bucle de reinicios.
echo "[entrypoint] Ejecutando migraciones..."
php artisan migrate --force || echo "[entrypoint] AVISO: las migraciones fallaron. Revisa que el servicio MySQL este conectado."

echo "[entrypoint] Arrancando Apache..."
exec apache2-foreground
