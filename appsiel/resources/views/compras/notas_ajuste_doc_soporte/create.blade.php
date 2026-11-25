@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	@if (count($errors) > 0)
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="container-fluid">
		<div class="marco_formulario">

			<h4>Nueva nota de ajuste</h4>
			<hr>
			{{ Form::open(['url'=> $form_create['url'],'id'=>'form_create']) }}
				<div class="row" style="margin: 5px;">{{ Form::bsButtonsForm2($url_volver) }}</div>

				<div style="border: solid 1px #ddd; padding: 8px; margin-bottom: 10px;">
					<b>Documento soporte:</b> {{ $proveedor->documento_transaccion_prefijo_consecutivo }} &nbsp; <b>Fecha:</b> {{ $ds->fecha }}
					<br/>
					<b>Proveedor:</b> {{ $proveedor->tercero_nombre_completo }}
					<br/>
					<b>Valor del documento:</b> ${{ number_format($ds->valor_total, 0, ',', '.') }} &nbsp; <b>Forma de pago:</b> {{ ucfirst($ds->forma_pago) }}
					@if($saldo_cxp !== null)
						<br/>
						<b>Saldo pendiente por pagar al proveedor:</b> ${{ number_format($saldo_cxp, 0, ',', '.') }}
					@endif
				</div>

				<div class="form-group">
					<label for="concepto"><b>Concepto de la nota</b></label>
					<select name="concepto" id="concepto" class="form-control" required="required">
						<option value="">Seleccionar...</option>
						@foreach($conceptos as $codigo => $descripcion)
							<option value="{{ $codigo }}" {{ (old('concepto', $concepto) == $codigo) ? 'selected' : '' }} {{ ($codigo == 2 && $hay_notas) ? 'disabled' : '' }}>{{ $codigo }} - {{ $descripcion }}{{ ($codigo == 2 && $hay_notas) ? ' (el documento ya tiene notas activas)' : '' }}</option>
						@endforeach
					</select>
				</div>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				<div id="div_lineas" style="display: none;">
					<p class="help-block">
						<span class="col_cantidad">Digite la cantidad a devolver en cada línea.</span>
						<span class="col_valor">Digite el valor base (sin IVA) a rebajar en cada línea.</span>
					</p>
					<div class="table-responsive">
						<table class="table table-bordered table-striped" style="font-size: 13px;">
							<thead>
								<tr>
									<th>Producto</th>
									<th class="text-right">Cant.</th>
									<th class="text-right">Base (sin IVA)</th>
									<th class="text-right">IVA</th>
									<th class="text-right">Cant. disponible</th>
									<th class="text-right">Base disponible</th>
									<th class="col_cantidad">Cantidad a devolver</th>
									<th class="col_valor">Valor base a rebajar</th>
								</tr>
							</thead>
							<tbody>
								@foreach($lineas as $l)
									<tr>
										<td>{{ $l['producto'] }}</td>
										<td class="text-right">{{ $l['cantidad'] + 0 }}</td>
										<td class="text-right">${{ number_format($l['base'], 2, ',', '.') }}</td>
										<td class="text-right">${{ number_format($l['iva'], 2, ',', '.') }}</td>
										<td class="text-right">{{ $l['cantidad_disponible'] + 0 }}</td>
										<td class="text-right">${{ number_format($l['base_disponible'], 2, ',', '.') }}</td>
										<td class="col_cantidad"><input type="number" class="form-control input-sm" name="lineas[{{ $l['id'] }}]" data-tipo="cantidad" min="0" max="{{ $l['cantidad_disponible'] + 0 }}" step="any" value="{{ old('lineas.'.$l['id']) }}"></td>
										<td class="col_valor"><input type="number" class="form-control input-sm" name="lineas[{{ $l['id'] }}]" data-tipo="valor" min="0" max="{{ $l['base_disponible'] + 0 }}" step="any" value="{{ old('lineas.'.$l['id']) }}"></td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>

				<div id="aviso_anulacion" class="alert alert-warning" style="display: none;">
					Se anulará el documento soporte completo: se devuelven todas las cantidades y se reversan sus efectos.
				</div>

				{{ Form::hidden('url_id',Input::get('id')) }}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}
				{{ Form::hidden('url_id_transaccion',Input::get('id_transaccion')) }}
				<input type="hidden" name="compras_doc_relacionado_id" value="{{ $ds->id }}">

			{{ Form::close() }}

		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			// Solo el input de la columna visible viaja con el formulario (ambos comparten name).
			function mostrar_columnas() {
				var c = $('#concepto').val();
				var porCantidad = (c == '1');
				var porValor = (c == '3' || c == '4' || c == '5');

				$('#div_lineas').toggle(porCantidad || porValor);
				$('#aviso_anulacion').toggle(c == '2');
				$('.col_cantidad').toggle(porCantidad);
				$('.col_valor').toggle(porValor);
				$('input[data-tipo="cantidad"]').prop('disabled', !porCantidad);
				$('input[data-tipo="valor"]').prop('disabled', !porValor);
			}

			$('#concepto').on('change', mostrar_columnas);
			mostrar_columnas();

			$('#btn_guardar').click(function(event){
				event.preventDefault();
				if ($('#concepto').val() == '') {
					alert('Seleccione el concepto de la nota.');
					$('#concepto').focus();
					return false;
				}
				$(this).off('click').addClass('disabled');
				$('#form_create').submit();
			});

			if ($('#fecha').val() == '') {
				$('#fecha').val( get_fecha_hoy() );
			}
		});
	</script>
@endsection
