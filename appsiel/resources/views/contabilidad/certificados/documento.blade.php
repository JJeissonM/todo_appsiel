<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Certificado de retenciones {{ $numero }}</title>
<style>
@page { margin: 1.5cm; } body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
h1 { font-size: 17px; text-align: center; } h2 { font-size: 13px; } table { border-collapse: collapse; width: 100%; margin: 12px 0; }
th,td { border: 1px solid #ccc; padding: 5px; } th { background: #eee; } .numero { text-align: right; white-space: nowrap; }
.aviso { border: 1px solid #a77; padding: 8px; } .firma { margin-top: 35px; } tr { page-break-inside: avoid; }
</style></head><body>
@if(!$emitido)<p class="aviso"><strong>PREVISUALIZACIÓN — NO EMITIDO</strong></p>@endif
<h1>{{ $c['retencion_total'] == 0 ? 'Constancia de compras sin retención' : 'Certificado de retención '.($c['filtros']['tipo_impuesto'] === 'ICA' ? 'de industria y comercio (ICA)' : 'en la fuente a título de renta') }}</h1>
<p><strong>Número:</strong> {{ $numero }} · <strong>Año gravable:</strong> {{ $c['anio'] }} · <strong>Fecha de expedición:</strong> {{ $fecha_expedicion }}</p>
<p><strong>Período:</strong> {{ $c['desde'] }} a {{ $c['hasta'] }}
@if($c['desde'] !== $c['anio'].'-01-01' || $c['hasta'] !== $c['anio'].'-12-31') — Certificado parcial del período seleccionado.@endif</p>
<h2>Agente retenedor</h2>
<p>{{ $c['empresa']['descripcion'] }} — NIT {{ $c['empresa']['numero_identificacion'] }}-{{ $c['empresa']['digito_verificacion'] }}<br>
Dirección: {{ isset($c['empresa']['direccion1']) ? $c['empresa']['direccion1'] : '' }}<br>
Ciudad de consignación de la retención: {{ $c['filtros']['ciudad_consignacion'] }}</p>
<h2>Beneficiario del pago o abono en cuenta</h2>
<p>{{ $c['tercero']['descripcion'] }}<br>Identificación: {{ $c['tercero']['numero_identificacion'] }}{{ isset($c['tercero']['digito_verificacion']) ? '-'.$c['tercero']['digito_verificacion'] : '' }}</p>
@if($c['filtros']['tipo_impuesto'] === 'ICA')
<p><strong>Jurisdicción:</strong> {{ $c['filtros']['municipio'] }} — Código DANE {{ $c['filtros']['codigo_municipio'] }}<br>
<strong>Norma municipal:</strong> {{ $c['filtros']['norma_ica'] }}</p>
@endif
<p>Se certifican las siguientes operaciones y retenciones practicadas al beneficiario durante el período indicado.</p>
<table><thead><tr><th>Concepto</th>@if($c['filtros']['tipo_impuesto']==='ICA')<th>Actividad ICA</th>@endif<th>Tarifa %</th>@if($c['filtros']['tipo_impuesto']==='ICA')<th>Tarifa ‰</th>@endif<th>Base sujeta ($)</th><th>Retención ($)</th></tr></thead><tbody>
@foreach($c['tasas'] as $t)
<tr><td>{{ $t['concepto'] }}</td>@if($c['filtros']['tipo_impuesto']==='ICA')<td>{{ $t['actividad_ica'] }}</td>@endif
<td class="numero">{{ number_format($t['tarifa'], 4, ',', '.') }}</td>@if($c['filtros']['tipo_impuesto']==='ICA')<td class="numero">{{ number_format($t['tarifa'] * 10, 4, ',', '.') }}</td>@endif
<td class="numero">{{ number_format($t['base']/100, 2, ',', '.') }}</td><td class="numero">{{ number_format($t['retencion']/100, 2, ',', '.') }}</td></tr>
@endforeach
</tbody></table>
<p><strong>Compra total de los documentos incluidos, con IVA:</strong> ${{ number_format($c['compra_total']/100, 2, ',', '.') }}<br>
<strong>Suma de bases por concepto y tarifa:</strong> ${{ number_format($c['base_total']/100, 2, ',', '.') }}<br>
<strong>Retención neta certificada:</strong> ${{ number_format($c['retencion_total']/100, 2, ',', '.') }}</p>
<p>Compras incluidas: {{ $c['filtros']['compras']==='todas' ? 'todas las compras contabilizadas del período, incluso sin retención' : 'documentos de compra con retención del impuesto seleccionado' }}. Los valores de compra no se duplican por tarifa. Las devoluciones se presentan con signo negativo.</p>
@if(!isset($c['filtros']['mostrar_detalle_operaciones']) || $c['filtros']['mostrar_detalle_operaciones'] !== 'No')
<h2>Detalle de operaciones</h2>
<table><thead><tr><th>Fecha</th><th>Documento / factura</th><th>Concepto del pago o abono</th><th>Compra total ($)</th><th>Con retención</th></tr></thead><tbody>
@foreach($c['documentos'] as $d)
<tr><td>{{ $d['fecha'] }}</td><td>{{ $d['documento'] }} / {{ $d['factura'] }}</td><td>{{ $d['concepto'] }}</td><td class="numero">{{ number_format($d['total']/100, 2, ',', '.') }}</td><td>{{ $d['con_retencion'] ? 'Sí' : 'No' }}</td></tr>
@endforeach
</tbody></table>
@endif
@if(!$emitido)
@foreach($c['bloqueos'] as $error)<p class="aviso">{{ $error }}</p>@endforeach
@foreach($c['avisos'] as $aviso)<p>{{ $aviso }}</p>@endforeach
@endif
<div class="firma">
@if(!empty($c['filtros']['firma_imagen']))<img src="{{ $c['filtros']['firma_imagen'] }}" style="max-width:180px;max-height:70px;"><br>
@elseif($c['filtros']['tipo_impuesto']==='ICA' || $c['filtros']['firma_fuente']==='manual')<p>__________________________________<br>Firma del agente retenedor — requiere firma antes de su entrega.</p>@endif
<strong>{{ $c['filtros']['responsable'] }}</strong><br>Identificación: {{ $c['filtros']['identificacion_responsable'] }}<br>{{ !empty($c['filtros']['titulo_responsable']) ? $c['filtros']['titulo_responsable'] : 'Responsable del certificado' }}
</div>
@if($c['filtros']['tipo_impuesto']==='FUENTE')
<p>Certificación de otros conceptos conforme al artículo 381 del Estatuto Tributario.
@if($c['filtros']['firma_fuente']==='computador')Generado por computador por persona jurídica, sin firma autógrafa, conforme al artículo 1.6.1.12.12 del Decreto 1625 de 2016.@endif</p>
@else<p>Certificación de retenciones de ICA correspondiente a la jurisdicción y norma municipal indicadas.</p>@endif
@if($c['retencion_total']==0)<p>No acredita retenciones practicadas: se emite como constancia de operaciones del período.</p>@endif
</body></html>
