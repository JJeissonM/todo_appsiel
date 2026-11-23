<?php
	$notas_ajuste = App\FacturacionElectronica\NotaAjusteDocSoporte::where('core_tipo_transaccion_id', App\FacturacionElectronica\NotaAjusteDocSoporte::TRANSACCION_ID)
		->where('compras_doc_relacionado_id', $doc_encabezado->id)
		->orderBy('id')
		->get();
	$modelo_nota_id = DB::table('sys_modelos')->where('name_space', 'App\FacturacionElectronica\NotaAjusteDocSoporte')->value('id');
?>
@if($notas_ajuste->count() > 0)
	<div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Notas de ajuste</div>
	<div class="table-responsive">
		<table class="table table-bordered table-striped">
			{{ Form::bsTableHeader(['Documento','Fecha','Concepto','Valor total','Estado']) }}
			<tbody>
				@foreach($notas_ajuste as $nota)
					<?php
						$prefijo = $nota->tipo_documento_app;
						if ($nota->estado == 'Anulado') {
							$estado_nota = 'Anulada';
						} elseif ($nota->enviado_electronicamente()) {
							$estado_nota = 'Enviada';
						} else {
							$estado_nota = 'Sin enviar';
						}
					?>
					<tr>
						<td class="text-center">
							<a href="{{ url('compras_notas_ajuste_doc_soporte/'.$nota->id.'?id='.Input::get('id').'&id_modelo='.$modelo_nota_id.'&id_transaccion='.$nota->core_tipo_transaccion_id) }}" target="_blank">{{ $prefijo ? $prefijo->prefijo : '' }} {{ $nota->consecutivo }}</a>
						</td>
						<td>{{ $nota->fecha }}</td>
						<td>{{ $nota->nota_ajuste_concepto }} - {{ $nota->concepto_descripcion() }}</td>
						<td class="text-right">${{ number_format(abs($nota->valor_total), 0, ',', '.') }}</td>
						<td>{{ $estado_nota }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endif
