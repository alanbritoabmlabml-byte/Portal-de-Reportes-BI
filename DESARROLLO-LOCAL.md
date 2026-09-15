# Portafolio de Reportes BI · desarrollo local

Portal interno de Plásticos Carmen: inicio de sesión, menú de tarjetas con
los accesos de la empresa y los tableros de Power BI por departamento y área,
con control de quién ve cada tablero. Comparte la identidad visual del
Portafolio de Transformación (`marca.css`, logotipos, tipografía Poppins).

## Verlo funcionando: doble clic

Descomprime el zip **fuera de OneDrive** (por ejemplo en `C:\dev\portafolio-reportes-bi`)
y haz doble clic en **`iniciar-reportes-bi.bat`**. Revisa lo que falta,
instala las dependencias, crea la base con los datos de ejemplo y abre el
navegador en <http://localhost:8010> (el portafolio usa el 8000, así pueden
correr los dos a la vez).

| Cuenta | Correo | Clave | Qué ve |
| --- | --- | --- | --- |
| Administrador | `amoscoso@plasticoscarmen.com` | `password` | Todo + panel de administración |
| Administrador | `ktercero@plasticoscarmen.com` | `password` | Todo + panel de administración |
| Usuario | `emoscoso@plasticoscarmen.com` | `password` | Tableros de dirección y gerenciales |
| Usuario | `rrhh.prueba@plasticoscarmen.com` | `password` | Área RRHH + tableros públicos |
| Usuario | `planta.prueba@plasticoscarmen.com` | `password` | Supervisión de producción + públicos |
| Usuario | `usuario.prueba@plasticoscarmen.com` | `password` | Solo tableros públicos |

Lo único que hace falta tener instalado es **PHP 8.3 o superior y Composer**
(en Windows lo más corto es [Laragon Full](https://laragon.org/download/)).
La primera vez tarda unos minutos porque baja las dependencias.

**No hace falta MySQL.** En local la base es SQLite (`database/database.sqlite`).
Para producción edita el `.env` y pon `DB_CONNECTION=mysql` con sus
credenciales; las líneas ya están ahí, comentadas.

La clave `password` viene del seeder y **solo sirve en local**. La base de
usuarios definitiva se cargará sobre la misma tabla `users` cuando se cierren
las versiones; el panel de administración queda para altas y ajustes.

## Qué hay dentro

| Pantalla | Ruta | Quién |
| --- | --- | --- |
| Acceso | `/login` | Todos |
| Menú de tarjetas (favoritos, accesos, tarjeta Reportes BI desplegable, tableros por departamento) | `/` | Con sesión |
| Tableros de un área, cada Power BI en su iframe | `/bi/{area}` | Quien tenga al menos un reporte visible |
| Notificaciones | `/notificaciones` | Con sesión |
| Reportes BI: links de iframe, tipo, área y **quién puede verlos** | `/admin/reportes` | Administrador |
| Departamentos y áreas | `/admin/estructura` | Administrador |
| Tarjetas del menú | `/admin/tarjetas` | Administrador |
| Usuarios y avisos a todos | `/admin/usuarios` | Administrador |

### Reglas de visibilidad

- Un reporte **público** lo ve cualquier usuario con sesión.
- Un reporte **no público** lo ven solo los usuarios marcados en «Accesos» y los administradores.
- Un área aparece en el menú solo si el usuario tiene al menos un reporte visible en ella.
- Al dar acceso a un usuario, recibe una notificación en la campana.

### Reemplazar los tableros de muestra

Los reportes nacen con `url_iframe = demo`, que muestra un tablero de ejemplo
generado por el sistema. En **Administración → Reportes BI → Editar** pega la
URL de inserción de Power BI (Archivo → Insertar informe → Sitio web o portal);
el campo acepta la URL sola o el código `<iframe>` completo.

## Arranque manual, si prefieres la terminal

```bash
composer install
php preparar-local.php
php artisan key:generate
type nul > database\database.sqlite
php artisan migrate --seed
php artisan serve --port=8010
```

## Antes de dar por cerrado un cambio

```bash
php artisan test --compact     # 46 pruebas, todas deben quedar en verde
vendor/bin/pint --dirty        # formato PHP del proyecto
```

## Notas del proyecto

- Las vistas **no** usan `@vite`: CSS y JS viven en `public/css` y `public/js` y se
  sirven directos (igual que en el portafolio). `npm install` no hace falta.
- `public/css/marca.css`, `auth.css`, `panel-crud.css` y `tabla-tarjetas.css` son
  copias de las del portafolio: si allí cambia la paleta, se copian de nuevo.
- Las ilustraciones de las tarjetas son SVG en `public/img/tarjetas/`. Una tarjeta
  también acepta una captura (`.png`) o una URL absoluta como imagen.
- La tipografía Poppins se carga desde Google Fonts; sin internet la aplicación
  funciona igual, solo cambia la fuente.
- Para reconstruir la base desde cero: `php artisan migrate:fresh --seed`.

## Requisitos, en detalle

| Pieza | Versión | ¿Obligatorio? |
| --- | --- | --- |
| PHP | 8.3+ (producción corre 8.5), con `pdo_sqlite`, `sqlite3`, `mbstring`, `fileinfo`, `openssl`, `dom` | Sí |
| Composer | 2.x | Sí |
| MySQL / MariaDB | 10.11+ | No — solo para replicar producción |
| Laravel | 13.25 (`composer.lock`) | La instala Composer |
