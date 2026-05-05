<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Dashboard de riesgo — SIDERAE-Blenkir</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .muted { color: #666; font-size: 10px; margin-bottom: 12px; }
        table.meta { margin-bottom: 16px; width: 100%; border-collapse: collapse; }
        table.meta td { padding: 2px 8px 2px 0; vertical-align: top; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        table.data th { background: #f5f5f5; font-size: 10px; }
        .filtros { font-size: 10px; margin: 8px 0 12px; }
        ul { margin: 4px 0; padding-left: 18px; }
        .footnote { margin-top: 20px; font-size: 9px; color: #666; border-top: 1px solid #ddd; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>Reporte de dashboard — Prototipo académico SIDERAE-Blenkir</h1>
    <p class="muted">Generado {{ $generado_at }} · Usuario {{ $usuario_email }}</p>

    @if(!empty(array_filter($filtros_aplicados)))
        <div class="filtros">
            <strong>Filtros aplicados:</strong>
            <ul>
                @if(!empty($filtros_aplicados['sede']))<li>Sede: {{ $filtros_aplicados['sede'] }}</li>@endif
                @if(!empty($filtros_aplicados['nivel']))<li>Nivel educativo: {{ $filtros_aplicados['nivel'] }}</li>@endif
                @if(!empty($filtros_aplicados['grado']))<li>Grado: {{ $filtros_aplicados['grado'] }}</li>@endif
                @if(!empty($filtros_aplicados['seccion']))<li>Sección: {{ $filtros_aplicados['seccion'] }}</li>@endif
                @if(!empty($filtros_aplicados['nivel_riesgo']))<li>Nivel de riesgo (último índice): {{ $filtros_aplicados['nivel_riesgo'] }}</li>@endif
            </ul>
        </div>
    @else
        <p class="filtros">Sin filtros (todos los estudiantes).</p>
    @endif

    <table class="meta">
        <tr><td><strong>Total estudiantes (universo filtrado)</strong></td><td>{{ $total_estudiantes }}</td></tr>
    </table>

    <strong>Riesgos por nivel (último índice por estudiante en universo)</strong>
    <table class="data">
        <tr><th>Nivel</th><th>Cantidad</th><th>% sobre con índice</th></tr>
        <tr><td>Alto</td><td>{{ $riesgos_por_nivel['alto'] }}</td><td>{{ $porcentajes_riesgo['alto'] }}%</td></tr>
        <tr><td>Medio</td><td>{{ $riesgos_por_nivel['medio'] }}</td><td>{{ $porcentajes_riesgo['medio'] }}%</td></tr>
        <tr><td>Bajo</td><td>{{ $riesgos_por_nivel['bajo'] }}</td><td>{{ $porcentajes_riesgo['bajo'] }}%</td></tr>
    </table>

    <strong style="display:block;margin-top:14px;">Alertas por estado (estudiantes en universo)</strong>
    <table class="data">
        <tr><th>Estado</th><th>Cantidad</th><th>% sobre total alertas</th></tr>
        <tr><td>Pendiente</td><td>{{ $alertas_por_estado['pendiente'] }}</td><td>{{ $porcentajes_alertas['pendiente'] }}%</td></tr>
        <tr><td>En atención</td><td>{{ $alertas_por_estado['en_atencion'] }}</td><td>{{ $porcentajes_alertas['en_atencion'] }}%</td></tr>
        <tr><td>Cerrada</td><td>{{ $alertas_por_estado['cerrada'] }}</td><td>{{ $porcentajes_alertas['cerrada'] }}%</td></tr>
    </table>

    <strong style="display:block;margin-top:14px;">Últimos índices registrados (hasta {{ count($ultimos_riesgos) }})</strong>
    @if(empty($ultimos_riesgos))
        <p class="muted">Sin registros en el período seleccionado.</p>
    @else
        <table class="data">
            <tr>
                <th>Estudiante</th>
                <th>Código</th>
                <th>Índice</th>
                <th>Nivel</th>
                <th>Fecha</th>
            </tr>
            @foreach($ultimos_riesgos as $fila)
                <tr>
                    <td>{{ $fila['estudiante'] }}</td>
                    <td>{{ $fila['codigo'] }}</td>
                    <td>{{ number_format($fila['indice'], 4, '.', '') }}</td>
                    <td>{{ $fila['nivel'] }}</td>
                    <td>{{ $fila['fecha'] ?? '—' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <p class="footnote">Prototipo académico SIDERAE-Blenkir — sin logo institucional (RF-16.3 pendiente de verificar si se dispone de recurso). Exportación básica Sprint 6B.</p>
</body>
</html>
