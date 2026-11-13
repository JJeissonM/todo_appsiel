<style>
.retenciones-reporte { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #111; }
.retenciones-reporte table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.retenciones-reporte th, .retenciones-reporte td { border: 1px solid #ddd; padding: 4px; vertical-align: top; }
.retenciones-reporte th { background: #eee; }
.retenciones-reporte .numero { text-align: right; white-space: nowrap; }
.retenciones-reporte thead { display: table-header-group; }
.retenciones-reporte tr { page-break-inside: avoid; }
</style>
<div class="retenciones-reporte">
    <h3 style="text-align:center;">Retenciones: Fuente, ICA e IVA</h3>
    <p style="text-align:center;">{{ $empresa->descripcion }} — NIT {{ $empresa->numero_identificacion }}-{{ $empresa->digito_verificacion }}<br>
        Desde {{ $filtros['fecha_desde'] }} hasta {{ $filtros['fecha_hasta'] }} · Retenciones {{ $filtros['naturaleza'] }} · {{ $filtros['tipo_impuesto'] ?: 'Todos los impuestos' }} · Detalla tercero: {{ $filtros['detalla_terceros'] === 'No' ? 'No' : 'Sí' }}</p>
    <p>Fuente e IVA: auxiliar de preparación del formulario 350 (estructura 2026). ICA: declaración de la jurisdicción municipal.
       Las casillas DIAN corresponden a retenciones practicadas. Las sufridas se presentan como soporte de los impuestos a favor.</p>
    @if($sin_clasificar)
        <p><strong>Hay {{ $sin_clasificar }} registros del período sin clasificación de impuesto. Revise la clasificación fiscal antes de declarar.</strong></p>
    @endif
    @if(count($pendientes))
        <p><strong>{{ count($pendientes) }} registros seleccionados requieren revisión. Sus valores se conservan en los totales.</strong> Consulte las observaciones del reporte.</p>
    @endif
    @if(!count($detalle))
        <p>No hay retenciones para los filtros seleccionados.</p>
    @endif

    @if($filtros['detalla_terceros'] === 'No' || in_array($filtros['vista'], ['completo', 'resumen']))
    <table id="myTable" class="table report-table" data-excel-export="resumen">
        <thead>
            <tr><th colspan="10">Resumen fiscal por período, concepto y naturaleza del tercero — {{ $filtros['fecha_desde'] }} / {{ $filtros['fecha_hasta'] }}</th></tr>
            <tr><th>Período</th><th>Impuesto</th><th>Concepto</th><th>Persona</th><th>Municipio / actividad ICA</th><th>Casilla base</th><th>Casilla retención</th><th>Base sujeta</th><th>Retención neta</th><th>Revisión</th></tr>
        </thead>
        <tbody>
        @foreach($resumen as $fila)
            <tr>
                <td>{{ $fila->periodo }}</td><td>{{ $fila->tipo_impuesto }}</td><td>{{ $fila->concepto_nombre }}</td>
                <td>{{ ['natural' => 'Natural', 'juridica' => 'Jurídica', 'sin_clasificar' => 'Sin clasificar'][$fila->persona] }}</td><td>{{ $fila->codigo_municipio }} / {{ $fila->actividad_ica }}</td>
                <td>{{ $fila->casilla_base }}</td><td>{{ $fila->casilla_retencion }}</td>
                <td class="numero">{{ number_format($fila->base_centavos / 100, 2, ',', '.') }}</td>
                <td class="numero">{{ number_format($fila->retencion_centavos / 100, 2, ',', '.') }}</td>
                <td>{{ implode('; ', $fila->observaciones) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot><tr><th colspan="7">TOTAL DE BASES Y RETENCIONES SELECCIONADAS</th><th class="numero">{{ number_format($base_total / 100, 2, ',', '.') }}</th><th class="numero">{{ number_format($retencion_total / 100, 2, ',', '.') }}</th><td></td></tr></tfoot>
    </table>
    <table class="table report-table" data-excel-export="casillas">
        <thead><tr><th colspan="5">Casillas DIAN: acumulados clasificados por período (estructura 2026)</th></tr>
            <tr><th>Período</th><th>Casilla</th><th>Concepto</th><th>Valor contable</th><th>Aproximación a miles</th></tr></thead>
        <tbody>
        @foreach($casillas as $casilla)
            <tr><td>{{ $casilla->periodo }}</td><td>{{ $casilla->casilla }}</td><td>{{ $casilla->concepto }}</td>
                <td class="numero">{{ number_format($casilla->centavos / 100, 2, ',', '.') }}</td>
                <td class="numero">{{ number_format(round($casilla->centavos / 100000) * 1000, 0, ',', '.') }}</td></tr>
        @endforeach
        </tbody>
    </table>
    @endif

    @if($filtros['detalla_terceros'] !== 'No' && in_array($filtros['vista'], ['completo', 'terceros']))
    <table class="table report-table" data-excel-export="terceros">
        <thead>
            <tr><th colspan="12">Acumulado por tercero, concepto y tarifa — retenciones {{ $filtros['naturaleza'] }}</th></tr>
            <tr><th>Período</th><th>Impuesto</th><th>Concepto</th><th>Identificación</th><th>DV</th><th>Nombre / razón social</th><th>Persona</th><th>Municipio / actividad</th><th>Tarifa %</th><th>Tarifa ICA ‰</th><th>Base</th><th>Retención</th></tr>
        </thead>
        <tbody>
        @foreach($terceros as $fila)
            <tr><td>{{ $fila->periodo }}</td><td>{{ $fila->tipo_impuesto }}</td><td>{{ $fila->concepto_nombre }}</td>
                <td style="mso-number-format:'\@';">{{ $fila->numero_identificacion }}</td><td>{{ $fila->digito_verificacion }}</td><td>{{ $fila->tercero ?: 'Sin identificar' }}</td>
                <td>{{ ['natural' => 'Natural', 'juridica' => 'Jurídica', 'sin_clasificar' => 'Sin clasificar'][$fila->persona] }}</td><td>{{ $fila->codigo_municipio }} / {{ $fila->actividad_ica }}</td>
                <td class="numero">{{ $fila->tasa_retencion === null ? 'Pendiente' : number_format($fila->tasa_retencion, 4, ',', '.') }}</td><td class="numero">@if($fila->tipo_impuesto === 'ICA' && $fila->tasa_retencion !== null){{ number_format($fila->tasa_retencion * 10, 4, ',', '.') }}@endif</td>
                <td class="numero">{{ number_format($fila->base_centavos / 100, 2, ',', '.') }}</td><td class="numero">{{ number_format($fila->retencion_centavos / 100, 2, ',', '.') }}</td></tr>
        @endforeach
        </tbody>
        <tfoot><tr><th colspan="10">TOTAL</th><th class="numero">{{ number_format($base_total / 100, 2, ',', '.') }}</th><th class="numero">{{ number_format($retencion_total / 100, 2, ',', '.') }}</th></tr></tfoot>
    </table>
    @endif

    @if($filtros['detalla_terceros'] !== 'No' && (in_array($filtros['vista'], ['completo', 'detalle']) || count($pendientes)))
    <table class="table report-table" data-excel-export="detalle">
        <thead><tr><th colspan="12">Detalle y conciliación por documento</th></tr>
            <tr><th>Fecha</th><th>Documento</th><th>Impuesto</th><th>Retención / concepto</th><th>Identificación / tercero</th><th>Persona</th><th>Municipio / actividad</th><th>Tarifa %</th><th>Base</th><th>Retención</th><th>Certificado</th><th>Observaciones</th></tr></thead>
        <tbody>
        @foreach($detalle as $fila)
            <tr><td>{{ $fila->fecha }}</td>
                <td><a href="{{ url('enlace_show_documento/14/'.$fila->core_tipo_transaccion_id.'/'.$fila->core_tipo_doc_app_id.'/'.$fila->consecutivo) }}" target="_blank" rel="noopener noreferrer">{{ $fila->prefijo }} {{ $fila->consecutivo }}</a></td>
                <td>{{ $fila->tipo_impuesto }}</td><td>{{ $fila->retencion }} / {{ $fila->concepto_nombre }}</td>
                <td>{{ $fila->numero_identificacion }}-{{ $fila->digito_verificacion }} {{ $fila->tercero }}</td>
                <td>{{ ['natural' => 'Natural', 'juridica' => 'Jurídica', 'sin_clasificar' => 'Sin clasificar'][$fila->persona] }}</td><td>{{ $fila->codigo_municipio }} / {{ $fila->actividad_ica }}</td>
                <td class="numero">{{ $fila->tasa_retencion === null ? 'Pendiente' : number_format($fila->tasa_retencion, 4, ',', '.') }}</td>
                <td class="numero">{{ number_format($fila->base_centavos / 100, 2, ',', '.') }}</td><td class="numero">{{ number_format($fila->retencion_centavos / 100, 2, ',', '.') }}</td>
                <td>{{ $fila->numero_certificado }}</td><td>{{ implode('; ', $fila->observaciones) }}</td></tr>
        @endforeach
        </tbody>
        <tfoot><tr><th colspan="8">TOTAL</th><th class="numero">{{ number_format($base_total / 100, 2, ',', '.') }}</th><th class="numero">{{ number_format($retencion_total / 100, 2, ',', '.') }}</th><td colspan="2"></td></tr></tfoot>
    </table>
    @endif
    <p>Las bases se acumulan por retención y no representan el total de compras. Las cifras contables se muestran sin redondeo a miles; la tabla DIAN aproxima los acumulados por casilla del período después de sumar todos los terceros.
       Las sanciones, saldos de declaraciones anteriores y demás ajustes se diligencian por separado. Los pagos al exterior requieren completar el anexo respectivo.</p>
    <p>Referencia: <a href="{{ \App\Contabilidad\Services\Retenciones\ConceptosDeclaracion::FUENTE }}">Formulario 350 DIAN, 2026</a>.
        La jurisdicción de ICA corresponde a la operación y no se deduce del domicilio del tercero.</p>
</div>
