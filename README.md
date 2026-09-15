# Portafolio de Reportes BI

[![Pruebas](https://github.com/alanbritoabmlabml-byte/Portal-de-Reportes-BI/actions/workflows/pruebas.yml/badge.svg)](https://github.com/alanbritoabmlabml-byte/Portal-de-Reportes-BI/actions/workflows/pruebas.yml)
[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/alanbritoabmlabml-byte/Portal-de-Reportes-BI)

Portal interno de **Plásticos Carmen** construido con Laravel 13 y PHP 8.3+.
Inicio de sesión, menú de tarjetas con los accesos de la empresa (portafolio,
Control de Asistencia, SharePoint, sitios públicos, Microsoft 365) y los
tableros de Power BI por departamento y área, con control por usuario de qué
tablero puede ver cada quien. Misma identidad visual que el Portafolio de
Transformación.

## Verlo funcionando

| Dónde | Cómo |
| --- | --- |
| En tu PC | Doble clic en `iniciar-reportes-bi.bat` → <http://localhost:8010>. Detalles en **DESARROLLO-LOCAL.md**. |
| En la nube | Botón **Deploy to Render** de arriba (plan gratuito) o cualquier host que acepte un `Dockerfile`. Detalles abajo. |

Cuentas de prueba (clave `password`, solo para la demo):
`amoscoso@plasticoscarmen.com` (administrador) · `rrhh.prueba@plasticoscarmen.com` · `planta.prueba@plasticoscarmen.com` · `usuario.prueba@plasticoscarmen.com`.

## Despliegue en la nube

El repositorio trae un `Dockerfile` (PHP 8.4, dependencias instaladas, base
SQLite sembrada con datos de ejemplo) y un `render.yaml` (blueprint).

**Render (gratis, un clic).** Con el repositorio en GitHub: botón *Deploy to
Render* → autorizar GitHub → *Apply*. En unos minutos queda en
`https://portafolio-reportes-bi.onrender.com`. El plan free duerme tras 15 min
sin uso (el primer acceso tarda ~30 s) y su disco es efímero: la base se vuelve
a sembrar en cada despliegue, que es justo lo que se quiere en una demo.

**Laravel Cloud, Railway, Fly.io.** Cualquiera que construya un `Dockerfile`
funciona igual. Variables mínimas: `APP_KEY` (o se genera al arrancar),
`APP_URL`, `PORT`. Para producción real: `DB_CONNECTION=mysql` con sus
credenciales, `APP_KEY` fija y `APP_DEBUG=false`.

En cada arranque el contenedor ejecuta `php artisan portal:preparar`, que crea
la base si no existe, migra y siembra los datos de ejemplo solo si no hay
usuarios; una base ya poblada no se toca.

## Desarrollo

```bash
php artisan test --compact     # 48 pruebas Pest
vendor/bin/pint --dirty        # formato
```

Las pruebas corren también en GitHub Actions en cada push (`.github/workflows/pruebas.yml`).

Desarrollado por el Departamento de IT de Plásticos Carmen.
