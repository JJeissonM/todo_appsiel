<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 25px; }
        body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 10px; }
        h1 { margin: 0 0 5px; font-size: 18px; }
        .muted { color: #666; }
        .notice { margin: 12px 0; padding: 7px; background: #f3f3f3; border: 1px solid #ccc; }
        .summary, .lines { width: 100%; border-collapse: collapse; }
        .summary td { width: 50%; padding: 3px 5px; vertical-align: top; }
        .lines { margin-top: 14px; }
        .lines th, .lines td { border: 1px solid #999; padding: 5px; }
        .lines th { background: #51b796; color: #111; }
        .number { text-align: right; }
        .totals td { font-weight: bold; background: #eee; }
        .footer { margin-top: 18px; font-size: 8px; color: #555; word-break: break-all; }
    </style>
</head>
<body>
    <table class="summary">
        <tr>
            <td>
                <h1>{{ $empresa ? ($empresa->razon_social ?: $empresa->descripcion) : 'Empresa' }}</h1>
                <div>NIT: {{ $empresa ? $empresa->numero_identificacion : '' }}{{ $empresa && $empresa->digito_verificacion !== null ? ' - ' . $empresa->digito_verificacion : '' }}</div>
                <div>{{ $empresa ? $empresa->direccion1 : '' }}</div>
            </td>
            <td>
                <h1>Documento soporte de nómina electrónica</h1>
                <div><strong>Documento:</strong> {{ $documento->get_value_to_show() }}</div>
                <div><strong>Fecha:</strong> {{ $documento->fecha }}</div>
                <div><strong>Estado:</strong> {{ $documento->estado }}</div>
            </td>
        </tr>
    </table>

    <div class="notice">
        Representación gráfica generada por Appsiel a partir de la información almacenada del documento electrónico.
    </div>

    <table class="summary">
        <tr>
            <td><strong>Empleado:</strong> {{ $tercero ? $tercero->descripcion : '' }}</td>
            <td><strong>Identificación:</strong> {{ $tercero ? $tercero->numero_identificacion : '' }}</td>
        </tr>
        <tr>
            <td><strong>Cargo:</strong> {{ $contrato && $contrato->cargo ? $contrato->cargo->descripcion : '' }}</td>
            <td><strong>Sueldo:</strong> $ {{ number_format($contrato ? $contrato->sueldo : 0, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="lines">
        <thead>
            <tr><th>Concepto</th><th>Días</th><th>Horas</th><th>Devengo</th><th>Deducción</th></tr>
        </thead>
        <tbody>
            @foreach($accruals as $linea)
                <?php
                    $valorLinea = isset($linea['amount-ns']) ? $linea['amount-ns'] : (isset($linea['amount']) ? $linea['amount'] : 0);
                    $valorLinea += isset($linea['cesantias-interest']) ? $linea['cesantias-interest'] : 0;
                    $descripcionLinea = isset($linea['concept-description']) && $linea['concept-description'] !== ''
                        ? $linea['concept-description']
                        : (isset($linea['description']) && $linea['description'] !== '' ? $linea['description'] : (isset($linea['code']) ? $linea['code'] : ''));
                ?>
                <tr>
                    <td>{{ $descripcionLinea }}</td>
                    <td class="number">{{ isset($linea['days']) ? $linea['days'] : 0 }}</td>
                    <td class="number">{{ isset($linea['hours']) ? $linea['hours'] : 0 }}</td>
                    <td class="number">$ {{ number_format($valorLinea, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endforeach
            @foreach($deductions as $linea)
                <?php
                    $valorLinea = isset($linea['amount-ns']) ? $linea['amount-ns'] : (isset($linea['amount']) ? $linea['amount'] : 0);
                    $descripcionLinea = isset($linea['concept-description']) && $linea['concept-description'] !== ''
                        ? $linea['concept-description']
                        : (isset($linea['description']) && $linea['description'] !== '' ? $linea['description'] : (isset($linea['code']) ? $linea['code'] : ''));
                ?>
                <tr>
                    <td>{{ $descripcionLinea }}</td>
                    <td class="number">{{ isset($linea['days']) ? $linea['days'] : 0 }}</td>
                    <td class="number">{{ isset($linea['hours']) ? $linea['hours'] : 0 }}</td>
                    <td></td>
                    <td class="number">$ {{ number_format($valorLinea, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="totals">
                <td colspan="3">Totales</td>
                <td class="number">$ {{ number_format($totalDevengos, 0, ',', '.') }}</td>
                <td class="number">$ {{ number_format($totalDeducciones, 0, ',', '.') }}</td>
            </tr>
            <tr class="totals">
                <td colspan="4">Neto a pagar</td>
                <td class="number">$ {{ number_format($totalDevengos - $totalDeducciones, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if($resultado)
        <div class="footer">
            <div><strong>Estado DIAN:</strong> {{ $resultado->dian_status }}</div>
            @if($resultado->cune)<div><strong>CUNE:</strong> {{ $resultado->cune }}</div>@endif
        </div>
    @endif
</body>
</html>
