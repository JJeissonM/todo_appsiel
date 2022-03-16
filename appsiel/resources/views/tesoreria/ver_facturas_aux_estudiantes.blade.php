<?php
	// Hoy
	$fecha_hasta = date('Y-m-d');
	if ( !is_null( Input::get('fecha_hasta') ) )
	{
		$fecha_hasta = Input::get('fecha_hasta');
	}

	// Primero de enero del año actual
	$fecha_desde = date("Y-01-01");
	if ( !is_null( Input::get('fecha_desde') ) )
	{
		$fecha_desde = Input::get('fecha_desde');
	}
?>
@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<h3>
				Planes de pagos
			</h3>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">

                        <div class="form-group">
                            <label class="control-label col-sm-3" for="fecha_desde">Desde:</label>
                            <div class="col-sm-9">
                                {{ Form::date('fecha_desde', $fecha_desde, ['id'=>'fecha_desde']) }}
                            </div>
                        </div>
                        
                    </div>
                    <div class="col-md-4">

                        <div class="form-group">
                            <label class="control-label col-sm-3" for="fecha_hasta">Hasta:</label>
                            <div class="col-sm-9">
                                {{ Form::date('fecha_hasta', $fecha_hasta, ['id'=>'fecha_hasta']) }}
                            </div>
                        </div>
                        
                    </div>
                    <div class="col-md-4">
                        &nbsp;&nbsp;&nbsp;<a href="#" class="btn btn-primary btn-xs btn_actualizar"> Actualizar </a>
                    </div>
                </div>
            </div>

			<div class="table-responsive">
				<table class="table table-bordered table-striped" id="myTable">
					{{ Form::bsTableHeader(['Estudiante','Concepto','Mes','Vlr. a pagar','Vlr. pagado','Saldo pendiente','Fecha vencimiento','Estado','Detalle']) }}
					<tbody>
						@foreach($plan_pagos as $fila)
							<?php
								$fecha = explode("-",$fila->fecha_vencimiento);
								$nombre_mes = nombre_mes($fecha[1]);

								switch ($fila->estado) {
									case 'Pagada':
										$clase_tr = 'success';
										break;
									
									case 'Vencida':
										$clase_tr = 'danger';
										break;
									
									case 'Pendiente':
										$clase_tr = 'info';
										break;
									
									default:
										# code...
										break;
								}

								$id_modelo = config('matriculas.modelo_id_factura_estudiante'); // Factura de Estudiantes
								$id_transaccion = config('matriculas.transaccion_id_factura_estudiante'); // Factura de Ventas

								$cartera_id = $fila->id;

								$vtas_doc_encabezado_id = 0;
							?>
							<tr class="{{$clase_tr}}">
								<td class="text-center">{{ $fila->estudiante->tercero->descripcion }}</td>
								<td>{{ $fila->concepto->descripcion }}</td>
								<td>{{$nombre_mes}}</td>
								<td class="text-right"><?php echo number_format($fila->valor_cartera, 0, ',', '.')?></td>
								<td class="text-right"><?php echo number_format($fila->valor_pagado, 0, ',', '.')?></td>
								@php $pendiente = $fila->valor_cartera - $fila->valor_pagado @endphp
								<td class="text-right"><?php echo number_format($pendiente, 0, ',', '.')?></td>
								<td>{{$fila->fecha_vencimiento}}</td>
								<td>{{$fila->estado}}</td>
								<td>
									@if( empty( $fila->facturas_estudiantes->toArray() ) )
										Sin factura
									@else
										<?php
											$factura = $fila->facturas_estudiantes->where('cartera_estudiante_id', $cartera_id)->first();

											$vtas_doc_encabezado_id = $factura->vtas_doc_encabezado_id;
										?>
										
										<a class="btn btn-info btn-xs btn-detail" href="{{ url( 'ventas/' . $vtas_doc_encabezado_id . '?id=13&id_modelo='.$id_modelo.'&id_transaccion=' . $id_transaccion ) }}" title="Consultar Factura" target="_blank">{{ 'Factura ' . $factura->encabezado_factura->tipo_documento_app->prefijo . ' ' . $factura->encabezado_factura->consecutivo }} <i class="fa fa-eye"></i>  </a>
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>

				</table>
			</div>
		</div>
	</div>
@endsection
@section('scripts')
	<script type="text/javascript">

		var cambio_fecha_desde = 0;

		var cambio_fecha_hasta = 0;

		$(document).ready(function(){

			$('#fecha_desde').change(function(event){
				cambiar_enlace_boton_actualizar();
			});

			$('#fecha_hasta').change(function(event){
				$('#fecha_corte').val( $('#fecha_hasta').val() );				
				cambiar_enlace_boton_actualizar();
			});

			function cambiar_enlace_boton_actualizar()
			{
				var id = getParameterByName('id');

				$('.btn_actualizar').attr( 'href', "{{ url('facturas_estudiantes_index_facturas_plan_pagos')}}" + "?id=" + id + "&fecha_desde=" + $('#fecha_desde').val() + "&fecha_hasta=" + $('#fecha_hasta').val() );				
			}

			function getParameterByName( name )
			{
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}
		});

		
	</script>
@endsection

<?php
    function nombre_mes($num_mes){
        switch($num_mes){
            case '01':
                $mes="Enero";
                break;
            case '02':
                $mes="Febrero";
                break;
            case '03':
                $mes="Marzo";
                break;
            case '04':
                $mes="Abril";
                break;
            case '05':
                $mes="Mayo";
                break;
            case '06':
                $mes="Junio";
                break;
            case '07':
                $mes="Julio";
                break;
            case '08':
                $mes="Agosto";
                break;
            case '09':
                $mes="Septiembre";
                break;
            case '10':
                $mes="Octubre";
                break;
            case '11':
                $mes="Noviembre";
                break;
            case '12':
                $mes="Diciembre";
                break;
            default:
                $mes="----------";
                break;
        }
        return $mes;
    }
?>