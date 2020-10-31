@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<?php
				//$nombre_completo=$nombres." ".$estudiante->apellido1." ".$estudiante->apellido2;
			?>
			
			<h2>{{ $estudiante->nombre_completo }}</h2>
			<h4>Matrícula: {{ $codigo_matricula }} /  Curso: {{ $curso->descripcion }}</h4>

			<h3>Libreta de pagos</h3>
			<div class="table-responsive">
				<table class="table table-bordered table-striped">
					{{ Form::bsTableHeader(['Vlr. matrícula','Fecha inicio','Vlr. pensión anual','Núm. periodos','Vlr. pensión mensual','Estado']) }}
					<tbody>
						<tr class="info">
							<td><?php echo number_format($libreta->valor_matricula, 0, ',', '.')?></td>
							<td>{{$libreta->fecha_inicio}}</td>
							<td><?php echo number_format($libreta->valor_pension_anual, 0, ',', '.')?></td>
							<td>{{$libreta->numero_periodos}}</td>
							<td><?php echo number_format($libreta->valor_pension_mensual, 0, ',', '.')?></td>
							<td>{{$libreta->estado}}</td>
						</tr>
					</tbody>

				</table>
			</div>
			
			<h3>Recaudos realizados </h3>
			<div class="table-responsive">
				<table class="table table-bordered table-striped">

					{{ Form::bsTableHeader(['Fecha recaudo','Concepto','Mes','Vlr. recaudo','Acción','Creado por']) }}

					<tbody>
						@foreach($recaudos as $fila)
							<?php 
								$cartera = App\Tesoreria\TesoPlanPagosEstudiante::find($fila->id_cartera);
								$fecha = explode("-",$cartera->fecha_vencimiento);
								$nombre_mes = nombre_mes($fecha[1]);
							?>
							<tr>
								<td>{{$fila->fecha_recaudo}}</td>
								<td>{{$fila->concepto}}</td>
								<td>{{$nombre_mes}}</td>
								<td><?php echo number_format($fila->valor_recaudo, 0, ',', '.')?></td>
								<td>
									<a class="btn btn-info btn-xs btn-detail" href="{{ url('tesoreria/imprimir_comprobante_recaudo/'.$fila->id_cartera) }}" target="_blank"><i class="fa fa-btn fa-print"></i>&nbsp;Imprimir comprobante</a>

									@can('eliminar_recaudo_libreta')
										&nbsp;&nbsp;&nbsp;
										<a class="btn btn-danger btn-xs btn-detail" href="{{ url( 'tesoreria/eliminar_recaudo_libreta/'.$fila->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}" title="Eliminar"><i class="fa fa-btn fa-trash"></i>&nbsp;</a>
									@endcan
								</td>
								<td>{{$fila->creado_por}} : {{$fila->created_at}}</td>
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