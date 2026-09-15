<!DOCTYPE html>
<html lang="es" data-tema="claro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Trazabilidad · {{ $filtros['desde']->format('d/m/Y') }} – {{ $filtros['hasta']->format('d/m/Y') }}</title>
  <link rel="stylesheet" href="{{ asset('css/imprimible.css') }}?v={{ filemtime(public_path('css/imprimible.css')) }}">
</head>
<body>

<div class="hoja-acciones no-imprimir">
  <button type="button" onclick="window.print()">Guardar como PDF</button>
  <span>Se abre el diálogo de impresión del navegador: elige <b>Guardar como PDF</b>.</span>
</div>

<header class="hoja-cabecera">
  <div>
    <h1>Bitácora de trazabilidad</h1>
    <p>Portafolio de Reportes BI · Plásticos Carmen</p>
  </div>
  <dl class="hoja-meta">
    <div><dt>Rango</dt><dd>{{ $filtros['desde']->format('d/m/Y') }} – {{ $filtros['hasta']->format('d/m/Y') }}</dd></div>
    <div><dt>Emitido</dt><dd>{{ now()->format('d/m/Y H:i') }}</dd></div>
    <div><dt>Emitió</dt><dd>{{ auth()->user()?->name }}</dd></div>
  </dl>
</header>

<section class="resumen">
  <div><b>{{ number_format($metricas['sesiones']) }}</b><span>sesiones</span></div>
  <div><b>{{ number_format($metricas['usuarios']) }}</b><span>usuarios</span></div>
  <div><b>{{ \App\Models\Sesion::formatear($metricas['segundos_total']) }}</b><span>tiempo total</span></div>
  <div><b>{{ \App\Models\Sesion::formatear($metricas['promedio_sesion']) }}</b><span>promedio por sesión</span></div>
  <div><b>{{ number_format($metricas['paginas']) }}</b><span>páginas vistas</span></div>
  <div><b>{{ number_format($metricas['acciones']) }}</b><span>acciones</span></div>
</section>

<h2>Páginas donde más tiempo se pasa</h2>
<table>
  <thead><tr><th>Pantalla</th><th class="num">Visitas</th><th class="num">Tiempo</th></tr></thead>
  <tbody>
  @forelse ($metricas['paginas_top'] as $p)
    <tr><td>{{ $p['etiqueta'] }}</td><td class="num">{{ $p['visitas'] }}</td><td class="num">{{ \App\Models\Sesion::formatear($p['segundos']) }}</td></tr>
  @empty
    <tr><td colspan="3" class="vacia">Sin datos.</td></tr>
  @endforelse
  </tbody>
</table>

<h2>Usuarios más activos</h2>
<table>
  <thead><tr><th>Usuario</th><th class="num">Páginas</th><th class="num">Tiempo</th></tr></thead>
  <tbody>
  @forelse ($metricas['usuarios_top'] as $u)
    <tr><td>{{ $u['etiqueta'] }}</td><td class="num">{{ $u['visitas'] }}</td><td class="num">{{ \App\Models\Sesion::formatear($u['segundos']) }}</td></tr>
  @empty
    <tr><td colspan="3" class="vacia">Sin datos.</td></tr>
  @endforelse
  </tbody>
</table>

<h2 class="salto">Sesiones ({{ $sesiones->count() }})</h2>
<table>
  <thead>
    <tr><th>Usuario</th><th>Inicio</th><th>Fin</th><th class="num">Duración</th><th>Equipo</th><th>Estado</th></tr>
  </thead>
  <tbody>
  @forelse ($sesiones as $s)
    <tr>
      <td>{{ $s->usuario?->name ?? 'Cuenta eliminada' }}</td>
      <td>{{ $s->iniciada_at->format('d/m/Y H:i:s') }}</td>
      <td>{{ $s->cerrada_at?->format('d/m/Y H:i:s') ?? 'En curso' }}</td>
      <td class="num">{{ $s->duracionLegible() }}</td>
      <td>{{ $s->navegador }} · {{ $s->plataforma }}<br><small>{{ $s->ip }}</small></td>
      <td>{{ $s->abierta() ? 'Abierta' : ($s->motivo_cierre === 'salida' ? 'Cerró sesión' : 'Expirada') }}</td>
    </tr>
  @empty
    <tr><td colspan="6" class="vacia">Ninguna sesión en este rango.</td></tr>
  @endforelse
  </tbody>
</table>

<h2 class="salto">Bitácora ({{ $asientos->count() }})</h2>
<table>
  <thead>
    <tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Descripción</th><th>Pantalla</th><th>IP</th></tr>
  </thead>
  <tbody>
  @forelse ($asientos as $b)
    <tr>
      <td>{{ $b->created_at->format('d/m/Y H:i:s') }}</td>
      <td>{{ $b->usuario?->name ?? $b->usuario_nombre ?? '—' }}</td>
      <td>{{ $b->accion->etiqueta() }}</td>
      <td>{{ $b->descripcion }}</td>
      <td>{{ $b->ruta }}</td>
      <td>{{ $b->ip }}</td>
    </tr>
  @empty
    <tr><td colspan="6" class="vacia">Sin movimientos en este rango.</td></tr>
  @endforelse
  </tbody>
</table>

<footer class="hoja-pie">
  Documento generado automáticamente por el Portafolio de Reportes BI · {{ now()->format('d/m/Y H:i') }}
</footer>

</body>
</html>
