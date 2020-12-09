<?php 
	//dd( $factura->tipo_documento_app );
?>
<div class="container-fluid">
	{{ Form::open(array('url'=>'tesoreria/guardar_recaudo_cartera','class'=>'form-horizontal','id'=>'formulario')) }}

	    {{ Form::hidden('id_libreta',$libreta->id) }}
	    {{ Form::hidden('id_cartera',$cartera->id) }}
	    {{ Form::hidden('id_estudiante',$libreta->id_estudiante) }}
	    {{ Form::hidden('concepto',$cartera->concepto) }}
	    {{ Form::hidden('core_tercero_id', $estudiante->responsable_financiero()->tercero->id) }}
	    {{ Form::hidden('cliente_id', $factura->cliente_id) }}


	    {{ Form::hidden( 'vtas_doc_encabezado_id', Input::get('vtas_doc_encabezado_id') ) }}
	    <!-- id_doc es el ID del movimiento de CxC -->
	    {{ Form::hidden( 'id_doc', $id_doc ) }}

	    {{ Form::hidden( 'core_empresa_id', Auth::user()->empresa_id ) }}
	    {{ Form::hidden( 'core_tipo_transaccion_id', config('tesoreria.recaudo_tipo_transaccion_id') ) }}
	    {{ Form::hidden( 'core_tipo_doc_app_id', config('tesoreria.recaudo_tipo_doc_app_id') ) }}
	    {{ Form::hidden( 'teso_motivo_id', config('tesoreria.recaudo_motivo_id')) }}

	    {{ Form::hidden('url_id',Input::get('id'))}}
		{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}

		<div class="row" style="padding:5px;">
	        {{ Form::label('lbl_factura','Factura de ventas: ' . $factura->tipo_documento_app->prefijo . ' ' . $factura->consecutivo ,[]) }}
	    </div>

		<div class="row" style="padding:5px;">
			{{ Form::label('lbl_cpto','Concepto: ' . $cartera->concepto->descripcion, []) }}
	    </div>

		<div class="row" style="padding:5px;">
	        @php $valor_pendiente = $cartera->valor_cartera - $cartera->valor_pagado @endphp
			{{ Form::label('lbl_valor_pendiente','Valor pendiente: $'.number_format($valor_pendiente, 0, ',', '.'),[]) }}
	    </div>

		<div class="row" style="padding:5px;">
			    {{ Form::label('fecha_vencimiento','Fecha vencimiento: '.$cartera->fecha_vencimiento,[]) }}
	    </div>	

		<div class="row" style="padding:5px;">
	        {{ Form::bsFecha('fecha_recaudo', date('Y-m-d'), 'Fecha recaudo', null, ['id'=>'fecha_recaudo','required' => 'required']) }}
	    </div>

		<div class="row" style="padding:5px;">
			{{ Form::bsSelect( 'teso_medio_recaudo_id',null,'Medio de pago', App\Tesoreria\TesoMedioRecaudo::opciones_campo_select(),[ 'id' => 'teso_medio_recaudo_id', 'required' => 'required' ]) }}
	    </div>

		<div class="row" style="padding:5px;">
			{{ Form::bsSelect('teso_caja_id',null,'Caja', App\Tesoreria\TesoCaja::opciones_campo_select(),[ 'id' => 'teso_caja_id', 'required' => 'required' ]) }}
	    </div>

		<div class="row" style="padding:5px;">
			{{ Form::bsSelect('teso_cuenta_bancaria_id',null,'Banco', App\Tesoreria\TesoCuentaBancaria::opciones_campo_select(),[ 'id' => 'teso_cuenta_bancaria_id' ]) }}		
	    </div>

	    <div class="row" style="padding:5px;">
			<div class="form-group" style="display: none;">
			    {{ Form::label('cantidad_cuotas','','Cantidad cuotas',[]) }}
			    <input type="number" name="cantidad_cuotas" id="cantidad_cuotas" min="0" max="{{ $libreta->numero_periodos }}" value="1" class="form-control">
			</div>
	    </div>

		<div class="row" style="padding:5px;">
			{{ Form::bsText('valor_recaudo',$valor_pendiente,'Valor recaudo',['id'=>'valor_recaudo','required'=>'required']) }}
	    </div>

		{{ Form::hidden('valor_pendiente',$valor_pendiente,['id'=>'valor_pendiente']) }}

		{{ Form::hidden('creado_por',explode('@',Auth::user()->email)[0] ) }}	

	    {{ Form::bsButtonsForm('tesoreria/ver_plan_pagos/'.$cartera->id_libreta.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}

	{{Form::close()}}
</div>

@section('scripts')
	<script>
		$(document).ready(function(){

			var fecha_transaccion = getParameterByName('fecha_transaccion');

			$('#teso_medio_recaudo_id').val( $('#teso_medio_recaudo_id').find('option').first().next().val() );

			if (fecha_transaccion != '') 
			{
				var mi_fecha = fecha_transaccion.split("/");
				var nueva_fecha = mi_fecha[0]+"-"+mi_fecha[1]+"-"+mi_fecha[2];

				$('#fecha_recaudo').val( nueva_fecha );
				$('#teso_medio_recaudo_id').val(  $('#teso_medio_recaudo_id').find('option').last().val());

				$('#teso_cuenta_bancaria_id').val( 2 );

				$('#teso_caja_id').parent().parent().hide();
				$('#teso_caja_id').removeAttr('required');
				$('#teso_cuenta_bancaria_id').attr('required','required');

			}else{
				$('#teso_caja_id').val( $('#teso_caja_id').find('option').first().next().val() );
				$('#teso_cuenta_bancaria_id').removeAttr('required');
				$('#teso_cuenta_bancaria_id').parent().parent().hide();
			}


			$('#teso_medio_recaudo_id').change(function(){
				var valor = $(this).val().split('-');

				if ( valor != '' )
				{
					if ( valor[1] == 'Tarjeta bancaria' )
					{
						
						$('#teso_caja_id').val('');
						$('#teso_caja_id').parent().parent().hide();
						$('#teso_caja_id').removeAttr('required');
						
						$('#teso_cuenta_bancaria_id').parent().parent().show();
						$('#teso_cuenta_bancaria_id').val( $('#teso_cuenta_bancaria_id').find('option').first().next().val() );
						$('#teso_cuenta_bancaria_id').attr('required','required');

					}else{
						
						$('#teso_cuenta_bancaria_id').val('');
						$('#teso_cuenta_bancaria_id').parent().parent().hide();
						$('#teso_cuenta_bancaria_id').removeAttr('required');

						$('#teso_caja_id').parent().parent().show();
						$('#teso_caja_id').val( $('#teso_caja_id').find('option').first().next().val() );
						$('#teso_caja_id').attr('required','required');
					}
				}else{
					$('#teso_cuenta_bancaria_id').parent().parent().hide();
					$('#teso_caja_id').parent().parent().hide();
					$(this).focus();
				}			
			});

			$('#cantidad_cuotas').change(function(){
				var valor_pendiente = $("#valor_pendiente").val();
				var cantidad_cuotas = $("#cantidad_cuotas").val();
				valor_recaudo = valor_pendiente * cantidad_cuotas;
				$("#valor_recaudo").val(valor_recaudo);
				if(valor_recaudo>valor_pendiente){
					alert('Las cantidad de cuotas ingresadas superan al valor pendiente.');
					$("#cantidad_cuotas").val(1);
					$("#valor_recaudo").val(valor_pendiente);
				}
			});

			$('#valor_recaudo').keyup(function(){

				if ( !validar_input_numerico( $(this) ) ) { return false; }
				var valor_pendiente = parseInt($("#valor_pendiente").val(),10);
				var valor_recaudo = parseInt($('#valor_recaudo').val(),10);
				//alert(valor_recaudo);
				if(valor_recaudo>valor_pendiente){
					alert('El valor ingresado supera al valor pendiente.');
					$('#valor_recaudo').val(valor_pendiente);
				}
			});


			function getParameterByName(name) {
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}


			$('#bs_boton_guardar').click(function(){
				$($(this)).prop('disabled', true);

				if ( $('#valor_recaudo').val() <= 0 ) { alert('Debe ingresar un valor de recaudo.'); return false; }

				if ( !validar_requeridos() ) { return false; }
				
				$('#formulario').submit();
			});

		});
	</script>
@endsection