@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			@include('tesoreria.libretas_pagos.encabezados_datos_basicos', ['estudiante' => $estudiante ])

			@include('tesoreria.libretas_pagos.tabla_resumen_libreta_pagos', ['libreta' => $libreta ])
			
			<h3>Recaudos realizados </h3>
			<div class="table-responsive">
				<table class="table table-bordered table-striped">

					{{ Form::bsTableHeader(['Fecha recaudo','Documento','Concepto','Mes','Vlr. recaudo','Acción','Creado por']) }}

					<tbody>
						@foreach($recaudos as $recaudo)
							<?php 
								$cartera = $recaudo->registro_cartera_estudiante;
								$fecha = explode("-",$cartera->fecha_vencimiento);
								$nombre_mes = nombre_mes($fecha[1]);
							?>
							<tr>
								<td> {{$recaudo->fecha_recaudo}}</td>
								<td> {{ $recaudo->tipo_documento_app->prefijo }} {{ $recaudo->consecutivo }}</td>
								<td> {{$recaudo->elconcepto->descripcion}}</td>
								<td> {{$nombre_mes}}</td>
								<td> {{ number_format($recaudo->valor_recaudo, 0, ',', '.') }} </td>
								<td>
									<a class="btn btn-info btn-xs btn-detail" href="{{ url('tesoreria/imprimir_comprobante_recaudo/'.$recaudo->id_cartera) }}" target="_blank"><i class="fa fa-btn fa-print"></i>&nbsp;Imprimir comprobante</a>

									@can('eliminar_recaudo_libreta')
										&nbsp;&nbsp;&nbsp;
										<a class="btn btn-danger btn-xs btn-detail" href="{{ url( 'tesoreria/eliminar_recaudo_libreta/'.$recaudo->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}" title="Eliminar"><i class="fa fa-btn fa-trash"></i>&nbsp;</a>
									@endcan
								</td>
								<td>{{$recaudo->creado_por}} : {{$recaudo->created_at}}</td>
							</tr>
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