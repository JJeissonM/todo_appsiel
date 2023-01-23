@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	@if( is_null( $matricula_estudiante->estudiante->responsable_financiero() ) )
		<div class="container">
			<div class="alert alert-danger">
				<b>¡Advertencia!</b>
				<br>
				El estudiante no tiene responsable finanaciero asociado. No se podrán generar sus facturas mensuales.
				<br>
				Asignar responsable aquí: <a href="{{ url( 'matriculas/estudiantes/gestionresponsables/estudiante_id?id=1&id_modelo=29&estudiante_id=' .$matricula_estudiante->id_estudiante ) }}" target="_blank" title="Gestionar Responsables" class="btn btn-success btn-xs">  <i class="fa fa-arrow-right"></i> </a>
			</div>
		</div>
	@endif

	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<div class="row">
				<div class="col-md-12 botones-gmail">
					<a href="{{ url('/') . '/matriculas/show/' . $matricula_estudiante->id . '?id=1&id_modelo=19&id_transaccion=' }}" class="btn-gmail" title="Consultar matrícula"><i class="fa fa-btn fa-book"></i> </a>
				</div>
			</div>

			@include('tesoreria.libretas_pagos.encabezados_datos_basicos', [ 'estudiante' => $matricula_estudiante->estudiante, 'matricula' => $matricula_estudiante ])

			@include('tesoreria.libretas_pagos.tabla_resumen_libreta_pagos', [ 'libreta' => $libreta ])
			
			<h3>
				Plan de pagos
				<a class="btn btn-primary btn-xs btn-detail pull-right" href="{{ url('tesoreria/ver_recaudos/'.$libreta->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}" title="Consultar recaudos"><i class="fa fa-btn fa-search"></i>&nbsp;Consultar recaudos</a>
			</h3>

			@include('tesoreria.libretas_pagos.datos_documentos_anticipo')

			<div class="table-responsive">
				<table class="table table-bordered table-striped">
					{{ Form::bsTableHeader(['ID','Concepto','Mes','Vlr. a pagar','Vlr. pagado','Saldo pendiente','Fecha vencimiento','Estado','Acción']) }}
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
							@if($fila->concepto == null)
								<tr class="{{$clase_tr}}">
									<td colspan="2">Concepto errado para esta linea del plan de pagos: ID={{$fila->id}}</td>
								</tr>
							@else
								<tr class="{{$clase_tr}}">
									<td class="text-center">{{ $fila->id }}</td>
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
											<a class="btn btn-warning btn-xs btn-detail" href="{{ url( 'web/' . $fila->id . '/edit?id='.Input::get('id') . '&id_modelo=264' ) }}" title="Modificar"><i class="fa fa-edit"></i>&nbsp; </a>

											<a class="btn btn-success btn-xs btn-detail" href="{{ url('facturas_estudiantes/create?id='.Input::get('id').'&id_modelo='.$id_modelo.'&id_transaccion='.$id_transaccion.'&estudiante_id='.$fila->id_estudiante) . '&inv_producto_id=' . $fila->inv_producto_id  . '&libreta_id=' . $libreta->id  . '&cartera_id=' . $cartera_id  . '&valor_cartera='.$fila->valor_cartera }}" title="Facturar"><i class="fa fa-file"></i>&nbsp;Facturar</a>
										@else
											<?php
												$factura = $fila->facturas_estudiantes->where('cartera_estudiante_id', $cartera_id)->first();

												$vtas_doc_encabezado_id = $factura->vtas_doc_encabezado_id;
											?>
											
											<a class="btn btn-info btn-xs btn-detail" href="{{ url( 'ventas/' . $vtas_doc_encabezado_id . '?id=13&id_modelo=139&id_transaccion=' . $id_transaccion ) }}" title="Consultar Factura" target="_blank">{{ 'Factura ' . $factura->encabezado_factura->tipo_documento_app->prefijo . ' ' . $factura->encabezado_factura->consecutivo }} <i class="fa fa-eye"></i>  </a>

											@if( $fila->estado != 'Pagada' )
												<a class="btn btn-primary btn-xs btn-detail" href="{{ url('tesoreria/hacer_recaudo_cartera/'.$cartera_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&vtas_doc_encabezado_id=' . $vtas_doc_encabezado_id ) }}" title="Recaudar"><i class="fa fa-btn fa-money"></i>&nbsp;Recaudar</a>
											@endif
										@endif
									</td>
								</tr>
							@endif
						@endforeach
					</tbody>

				</table>
			</div>
		</div>
	</div>
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