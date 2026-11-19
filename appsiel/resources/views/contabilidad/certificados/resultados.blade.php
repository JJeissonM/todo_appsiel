<p>Se previsualizaron <strong>{{ count($certificados) }}</strong> certificados. Revise cada documento antes de emitir o enviar. Los rangos que abarcan varios años se separan por año gravable.</p>
@if(count($certificados))
<p><button type="button" class="btn btn-default" id="cert-descargar-lote">Descargar PDF individuales (ZIP)</button> <button type="button" class="btn btn-primary" id="cert-enviar-todos">Enviar seleccionados por correo</button> <span id="cert-progreso" role="status"></span></p>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th><label><input type="checkbox" id="cert-seleccionar-todos"> Todos</label></th><th>Proveedor</th><th>Período</th><th>Compra total</th><th>Base</th><th>Retención</th><th>Acciones</th><th>Resultado</th></tr></thead><tbody>
@foreach($certificados as $r)
<?php $c=$r['datos']; $correo=isset($c['tercero']['email']) ? $c['tercero']['email'] : ''; ?>
<tr class="cert-fila" data-token="{{ $r['token'] }}">
<td><input type="checkbox" class="cert-seleccion" {{ count($c['bloqueos']) ? 'disabled' : '' }}></td>
<td>{{ $c['tercero']['descripcion'] }}<br>{{ $correo ?: 'Sin correo registrado' }}</td><td>{{ $c['desde'] }} / {{ $c['hasta'] }}</td>
<td>{{ number_format($c['compra_total']/100,2,',','.') }}</td><td>{{ number_format($c['base_total']/100,2,',','.') }}</td><td>{{ number_format($c['retencion_total']/100,2,',','.') }}</td>
<td><a href="{{ url('contab_certificados_retenciones/'.$r['token']) }}" target="_blank" rel="noopener">Previsualizar</a>
@if(!count($c['bloqueos']))
<form action="{{ url('contab_certificados_retenciones/'.$r['token'].'/pdf') }}" method="POST" target="_blank" style="display:inline">{{ csrf_field() }} <button class="btn btn-default btn-sm">Emitir / descargar PDF</button></form>
@endif</td>
<td class="cert-estado">@foreach($c['bloqueos'] as $error)<div class="text-danger">{{ $error }}</div>@endforeach</td></tr>
@endforeach
</tbody></table></div>
@else<p class="alert alert-info">No hay operaciones contabilizadas para los filtros seleccionados.</p>@endif
