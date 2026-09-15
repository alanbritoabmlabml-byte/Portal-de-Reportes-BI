{{--
  Tablero de muestra. Se ve dentro del iframe mientras un reporte no tiene
  todavía su URL real de Power BI. Los números salen de una semilla fija por
  reporte, así que cada uno muestra cifras distintas pero estables.
--}}
@php
    mt_srand($reporte->id * 7919);
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep'];
    $serie = array_map(fn () => mt_rand(35, 100), $meses);
    $max = max($serie);
    $kpis = [
        ['Total del mes', number_format(mt_rand(1200, 98000), 0, ',', '.'), '+'.mt_rand(1, 14).'%'],
        ['Cumplimiento', mt_rand(78, 99).'%', '+'.mt_rand(0, 6).' pts'],
        ['Incidencias', (string) mt_rand(0, 24), '-'.mt_rand(1, 30).'%'],
        ['Promedio diario', number_format(mt_rand(40, 3200), 0, ',', '.'), '+'.mt_rand(0, 9).'%'],
    ];
    $categorias = ['Bolsas', 'Termoformado', 'Expandido', 'Inyección', 'Extrusión'];
    $partes = array_map(fn () => mt_rand(10, 40), $categorias);
    $suma = array_sum($partes);
    $puntos = [];
    foreach ($serie as $i => $v) {
        $puntos[] = round(40 + $i * (560 / (count($serie) - 1))).','.round(150 - ($v / $max) * 120);
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $reporte->titulo }} · muestra</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{--azul:#063381;--azul-medio:#2b56a6;--azul-tenue:#e8edf6;--rojo:#E00613;--tinta:#04205a;--texto:#232c42;--suave:#6a7592;--borde:#e0e5ef}
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:Poppins,"Segoe UI",system-ui,sans-serif;background:#f4f6fa;color:var(--texto);font-size:13px;padding:16px;min-height:100vh}
  header{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px}
  h1{font-size:17px;color:var(--azul);font-weight:600}
  header p{color:var(--suave);font-size:12px;margin-top:2px}
  .marca{font-size:10px;letter-spacing:1.6px;text-transform:uppercase;font-weight:700;color:var(--azul);background:#fff;border:1px solid var(--borde);border-radius:20px;padding:5px 11px}
  .kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:12px}
  .kpi{background:#fff;border:1px solid var(--borde);border-radius:10px;padding:12px 14px;border-top:3px solid var(--azul)}
  .kpi:nth-child(3){border-top-color:var(--rojo)}
  .kpi span{display:block;font-size:10.5px;letter-spacing:.8px;text-transform:uppercase;color:var(--suave);font-weight:600}
  .kpi b{display:block;font-size:22px;color:var(--tinta);margin-top:4px;font-variant-numeric:tabular-nums}
  .kpi em{font-style:normal;font-size:11px;color:#15693a;font-weight:600}
  .kpi:nth-child(3) em{color:#a8040e}
  .paneles{display:grid;grid-template-columns:2fr 1fr;gap:10px}
  .panel{background:#fff;border:1px solid var(--borde);border-radius:10px;padding:12px 14px}
  .panel h2{font-size:12px;font-weight:600;color:var(--azul);margin-bottom:8px}
  svg{width:100%;height:auto;display:block}
  .barra{display:grid;grid-template-columns:90px 1fr 34px;align-items:center;gap:8px;font-size:12px;margin:6px 0}
  .barra i{display:block;height:10px;border-radius:5px;background:var(--azul-tenue);overflow:hidden}
  .barra i::after{content:"";display:block;height:100%;width:var(--w);background:var(--azul-medio);border-radius:5px}
  .barra:nth-child(3) i::after{background:var(--rojo)}
  .barra b{text-align:right;font-variant-numeric:tabular-nums;color:var(--tinta)}
  .aviso{margin-top:12px;font-size:11.5px;color:var(--suave);background:#fff;border:1px dashed #c6d0e4;border-radius:8px;padding:9px 12px}
  @media (max-width:640px){.paneles{grid-template-columns:1fr}}
</style>
</head>
<body>
<header>
  <div>
    <h1>{{ $reporte->titulo }}</h1>
    <p>{{ $reporte->area->nombre }} · {{ $reporte->tipo->etiqueta() }} · tablero de muestra</p>
  </div>
  <span class="marca">Plásticos Carmen · BI</span>
</header>

<section class="kpis">
  @foreach ($kpis as [$nombre, $valor, $variacion])
    <div class="kpi"><span>{{ $nombre }}</span><b>{{ $valor }}</b><em>{{ $variacion }} vs. mes anterior</em></div>
  @endforeach
</section>

<section class="paneles">
  <div class="panel">
    <h2>Evolución mensual</h2>
    <svg viewBox="0 0 620 180" role="img" aria-label="Evolución mensual">
      @for ($i = 0; $i <= 4; $i++)
        <line x1="40" y1="{{ 30 + $i * 30 }}" x2="600" y2="{{ 30 + $i * 30 }}" stroke="#e0e5ef" stroke-width="1"/>
      @endfor
      <polyline points="{{ implode(' ', $puntos) }}" fill="none" stroke="#063381" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"/>
      <polygon points="40,150 {{ implode(' ', $puntos) }} 600,150" fill="rgba(6,51,129,.08)"/>
      @foreach ($puntos as $i => $p)
        @php [$x, $y] = explode(',', $p); @endphp
        <circle cx="{{ $x }}" cy="{{ $y }}" r="4" fill="#fff" stroke="#063381" stroke-width="2.5"/>
        <text x="{{ $x }}" y="172" text-anchor="middle" font-size="11" fill="#6a7592" font-family="Poppins,sans-serif">{{ $meses[$i] }}</text>
      @endforeach
    </svg>
  </div>
  <div class="panel">
    <h2>Participación por línea</h2>
    @foreach ($categorias as $i => $cat)
      @php $pct = round($partes[$i] / $suma * 100); @endphp
      <div class="barra"><span>{{ $cat }}</span><i style="--w:{{ $pct }}%"></i><b>{{ $pct }}%</b></div>
    @endforeach
  </div>
</section>

<p class="aviso">Este tablero es una muestra generada por el sistema. Un administrador debe reemplazarlo pegando la URL de inserción del informe real de Power BI en <b>Administración → Reportes BI</b>.</p>
</body>
</html>
