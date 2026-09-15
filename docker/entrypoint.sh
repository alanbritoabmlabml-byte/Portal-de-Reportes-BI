#!/usr/bin/env bash
# Arranque del contenedor: prepara la base y sirve la aplicación.
#
# - APP_KEY: si el host no la define, se genera una por arranque. Sirve para
#   la demo; en producción fijarla como variable de entorno para que las
#   sesiones sobrevivan a los reinicios.
# - Base: `portal:preparar` crea el SQLite si hace falta, migra y siembra los
#   datos de ejemplo solo si no hay usuarios. Con disco efímero (Render free)
#   vuelve a sembrarse en cada despliegue, que es lo que se quiere en una demo.
set -euo pipefail

cd /app

# Laravel exige una clave de 32 bytes en formato base64:… . Si el host la
# entrega en otro formato (Render genera una cadena aleatoria cualquiera) se
# deriva de ella con SHA-256: misma entrada, misma clave en cada arranque.
case "${APP_KEY:-}" in
  base64:*)
    ;;
  "")
    export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
    echo "[arranque] APP_KEY generada para esta ejecución (define APP_KEY en el host para fijarla)."
    ;;
  *)
    export APP_KEY="$(php -r 'echo "base64:".base64_encode(hash("sha256", $argv[1], true));' "$APP_KEY")"
    echo "[arranque] APP_KEY normalizada al formato base64 de 32 bytes."
    ;;
esac

php artisan portal:preparar --no-interaction

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[arranque] Sirviendo en el puerto ${PORT}"
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
