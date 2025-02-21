@extends('layouts.academico_estudiante')

@section('content')
	@include('layouts.mensajes')
	
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			
			@include('tesoreria.libretas_pagos.encabezados_datos_basicos', ['estudiante' => $estudiante ])

			<p style="color: red;">Nota: Solo se muestra la libreta de pagos del año lectivo actual.</p>

			@if( !is_null($libreta) )
				<h3>
					Libreta de pagos
					<div class="pull-right">
						{{ Form::bsBtnPrint( url( 'tesoreria/imprimir_libreta/'.$libreta->id ) )  }}
					</div>

				</h3>
				<div class="table-responsive">
					<table class="table table-bordered table-striped">
						{{ Form::bsTableHeader(['Vlr. matrícula','Fecha inicio','Vlr. pensión anual','Núm. periodos','Vlr. pensión mensual','Estado']) }}
						<tbody>
								<tr class="info">
									<td class="text-right"><?php echo number_format($libreta->valor_matricula, 0, ',', '.')?></td>
									<td>{{$libreta->fecha_inicio}}</td>
									<td class="text-right"><?php echo number_format($libreta->valor_pension_anual, 0, ',', '.')?></td>
									<td>{{$libreta->numero_periodos}}</td>
									<td class="text-right"><?php echo number_format($libreta->valor_pension_mensual, 0, ',', '.')?></td>
									<td>{{$libreta->estado}}</td>
								</tr>
						</tbody>

					</table>
				</div>
				
				<h3>
					Plan de pagos
				</h3>
				<div class="table-responsive">
					<table class="table table-bordered table-striped">
						{{ Form::bsTableHeader(['Concepto','Mes','Vlr. a pagar','Vlr. pagado','Saldo pendiente','Fecha vencimiento','Estado','Acción']) }}
						<tbody>
							@foreach($cartera as $fila)
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
									//dd($fila);
								?>
								<tr class="{{$clase_tr}}">
									<td>{{$fila->concepto->descripcion}}</td>
									<td>{{$nombre_mes}}</td>
									<td class="text-right"><?php echo number_format($fila->valor_cartera, 0, ',', '.')?></td>
									<td class="text-right"><?php echo number_format($fila->valor_pagado, 0, ',', '.')?></td>
									@php $pendiente = $fila->valor_cartera - $fila->valor_pagado @endphp
									<td class="text-right"><?php echo number_format($pendiente, 0, ',', '.')?></td>
									<td>{{$fila->fecha_vencimiento}}</td>
									<td>{{$fila->estado}}</td>
									<td>
										@if($fila->estado!='Pagada')
											
										@else
											<a class="btn btn-info btn-xs btn-detail" href="{{ url('tesoreria/imprimir_comprobante_recaudo/'.$fila->id) }}"><i class="fa fa-btn fa-print"></i>&nbsp;Imprimir comprobante</a>
										@endif
									</td>
								</tr>
							@endforeach
						</tbody>

					</table>
				</div>
			@else
				<div class="alert alert-danger alert-dismissible">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				  <strong>Atención!</strong> 
				  <br> El estudiante no tiene libreta de pagos creada.
				</div>
			@endif
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