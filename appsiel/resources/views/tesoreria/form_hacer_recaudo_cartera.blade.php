
<?php
	
	$core_empresa_id = Auth::user()->empresa_id;
	
	// Obtengo la lista Para el campo medio de pago
	$medio_pago = App\Sistema\Campo::find(248); // Medio de pago (table_teso_medios_recaudo)
	$texto_opciones = $medio_pago->opciones;
	$tabla = substr($texto_opciones,6,strlen($texto_opciones)-1);
    $opciones = \DB::table($tabla)->get();
    foreach ($opciones as $opcion)
    {
    	$vec[$opcion->id.'-'.$opcion->comportamiento] = $opcion->descripcion;
    }

    // LISTA DE CAJAS
    $opciones2 = App\Tesoreria\TesoCaja::where('core_empresa_id',$core_empresa_id)->get();
    $cajas[''] = '';
    foreach ($opciones2 as $opcion)
    {
    	$cajas[$opcion->id] = $opcion->descripcion;
    }

    // LISTA DE CUENTAS BACARIAS
    $opciones3 = App\Tesoreria\TesoCuentaBancaria::leftJoin('teso_entidades_financieras','teso_entidades_financieras.id','=','teso_cuentas_bancarias.entidad_financiera_id')
                            ->where('core_empresa_id', $core_empresa_id)
                            ->select('teso_cuentas_bancarias.id','teso_cuentas_bancarias.descripcion AS cta_bancaria','teso_entidades_financieras.descripcion AS entidad_financiera')
                            ->get();
    $cuentas_bancaria[''] = '';
    foreach ($opciones3 as $opcion)
    {
    	$cuentas_bancaria[$opcion->id] = $opcion->entidad_financiera.': '.$opcion->cta_bancaria;
    }

    $estudiante = App\Matriculas\Estudiante::find($libreta->id_estudiante);
?>

{{ Form::open(array('url'=>'tesoreria/guardar_recaudo_cartera','class'=>'form-horizontal','id'=>'formulario')) }}

    {{ Form::hidden('id_libreta',$libreta->id) }}
    {{ Form::hidden('id_cartera',$cartera->id) }}
    {{ Form::hidden('id_estudiante',$libreta->id_estudiante) }}
    {{ Form::hidden('concepto',$cartera->concepto) }}
    {{ Form::hidden('core_tercero_id',$estudiante->core_tercero_id) }}

    {{ Form::hidden( 'core_empresa_id', $core_empresa_id)}}
    {{ Form::hidden( 'core_tipo_transaccion_id', 20)}}
    {{ Form::hidden( 'core_tipo_doc_app_id', 3)}}
    {{ Form::hidden( 'teso_motivo_id', 21)}} <!-- Motivo 21 = Abono  factura -->

    {{ Form::hidden('url_id',Input::get('id'))}}
	{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}

	<div class="row" style="padding:5px;">
		{{ Form::label('lbl_cpto','Concepto: '.$cartera->concepto,[]) }}
    </div>

	<div class="row" style="padding:5px;">
        @php $valor_pendiente = $cartera->valor_cartera - $cartera->valor_pagado @endphp
		{{ Form::label('valor_pendiente','Valor pendiente: $'.number_format($valor_pendiente, 0, ',', '.'),[]) }}
    </div>

	<div class="row" style="padding:5px;">
		    {{ Form::label('fecha_vencimiento','Fecha vencimiento: '.$cartera->fecha_vencimiento,[]) }}
    </div>	

	<div class="row" style="padding:5px;">
        {{ Form::bsFecha('fecha_recaudo', date('Y-m-d'), 'Fecha recaudo', null, ['id'=>'fecha_recaudo','required' => 'required']) }}
    </div>

	<div class="row" style="padding:5px;">
		{{ Form::bsSelect($medio_pago->name,null,$medio_pago->descripcion, $vec,['id'=>'teso_medio_recaudo_id','required' => 'required']) }}
    </div>

	<div class="row" style="padding:5px;">
		{{ Form::bsSelect('teso_caja_id',null,'Caja', $cajas,[ 'id' => 'teso_caja_id' ]) }}		
    </div>

	<div class="row" style="padding:5px;">
		{{ Form::bsSelect('teso_cuenta_bancaria_id',null,'Banco', $cuentas_bancaria,[ 'id' => 'teso_cuenta_bancaria_id' ]) }}		
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

@section('scripts')
	<script>
		$(document).ready(function(){

			var fecha_transaccion = getParameterByName('fecha_transaccion');

			if (fecha_transaccion != '') 
			{
				var mi_fecha = fecha_transaccion.split("/");
				var nueva_fecha = mi_fecha[0]+"-"+mi_fecha[1]+"-"+mi_fecha[2];

				$('#fecha_recaudo').val( nueva_fecha );
				$('#teso_medio_recaudo_id').val(  $('#teso_medio_recaudo_id').find('option').last().val());

				$('#teso_cuenta_bancaria_id').val( 2 );


				$('#teso_caja_id').parent().parent().hide();	

			}else{
				$('#teso_caja_id').val( $('#teso_caja_id').find('option').first().next().val() );
				$('#teso_cuenta_bancaria_id').parent().parent().hide();				
			}


			$('#teso_medio_recaudo_id').change(function(){
				var valor = $(this).val().split('-');
				if (valor!='') {
					if (valor[1]=='Tarjeta bancaria'){
						$('#teso_caja_id').val('');
						$('#teso_caja_id').parent().parent().hide();
						$('#teso_cuenta_bancaria_id').parent().parent().show();
						$('#teso_cuenta_bancaria_id').val( $('#teso_cuenta_bancaria_id').find('option').first().next().val() );
					}else{
						$('#teso_cuenta_bancaria_id').val('');
						$('#teso_cuenta_bancaria_id').parent().parent().hide();
						$('#teso_caja_id').parent().parent().show();
						$('#teso_caja_id').val( $('#teso_caja_id').find('option').first().next().val() );
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
				$('#formulario').submit();
			});

		});
	</script>
@endsection